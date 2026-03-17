/**
 * Offline Sync Module for SIM Penugasan Kuliah PWA
 * Handles IndexedDB storage and synchronization
 */

const DB_NAME = 'sim_tugas_db';
const DB_VERSION = 3;
const STORES = {
    MATA_KULIAH: 'mata_kuliah',
    TUGAS: 'tugas',
    REMINDERS: 'reminders',
    SYNC_QUEUE: 'sync_queue',
    CACHE_META: 'cache_meta'
};

class OfflineSync {
    constructor() {
        this.db = null;
        this.isOnline = navigator.onLine;
        this.syncInProgress = false;
        this.listeners = new Map();

        this.init();
    }

    async init() {
        await this.openDatabase();
        this.setupEventListeners();
        this.checkOnlineStatus();
    }

    /**
     * Open IndexedDB database
     */
    openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = () => {
                console.error('Failed to open database:', request.error);
                reject(request.error);
            };

            request.onsuccess = () => {
                this.db = request.result;
                console.log('Database opened successfully');
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Mata Kuliah store
                if (!db.objectStoreNames.contains(STORES.MATA_KULIAH)) {
                    const mkStore = db.createObjectStore(STORES.MATA_KULIAH, { keyPath: 'id' });
                    mkStore.createIndex('synced', 'synced', { unique: false });
                }

                // Tugas store
                if (!db.objectStoreNames.contains(STORES.TUGAS)) {
                    const tugasStore = db.createObjectStore(STORES.TUGAS, { keyPath: 'id' });
                    tugasStore.createIndex('mata_kuliah_id', 'mata_kuliah_id', { unique: false });
                    tugasStore.createIndex('status', 'status', { unique: false });
                    tugasStore.createIndex('synced', 'synced', { unique: false });
                }

                // Reminders store
                if (!db.objectStoreNames.contains(STORES.REMINDERS)) {
                    const remindersStore = db.createObjectStore(STORES.REMINDERS, { keyPath: 'id' });
                    remindersStore.createIndex('tugas_id', 'tugas_id', { unique: false });
                    remindersStore.createIndex('synced', 'synced', { unique: false });
                }

                // Sync queue for offline operations
                if (!db.objectStoreNames.contains(STORES.SYNC_QUEUE)) {
                    const syncStore = db.createObjectStore(STORES.SYNC_QUEUE, { keyPath: 'id', autoIncrement: true });
                    syncStore.createIndex('type', 'type', { unique: false });
                    syncStore.createIndex('timestamp', 'timestamp', { unique: false });
                    syncStore.createIndex('status', 'status', { unique: false });
                }

                // Cache metadata
                if (!db.objectStoreNames.contains(STORES.CACHE_META)) {
                    db.createObjectStore(STORES.CACHE_META, { keyPath: 'key' });
                }
            };
        });
    }

    /**
     * Setup online/offline event listeners
     */
    setupEventListeners() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.emit('online');
            this.showNotification('Koneksi kembali tersedia', 'success');
            this.syncPendingOperations();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.emit('offline');
            this.showNotification('Anda sedang offline. Data akan disimpan lokal.', 'warning');
        });
    }

    /**
     * Check current online status and update UI
     */
    checkOnlineStatus() {
        this.isOnline = navigator.onLine;
        this.emit(this.isOnline ? 'online' : 'offline');
        return this.isOnline;
    }

    /**
     * Generic method to save data to IndexedDB
     */
    async saveToStore(storeName, data) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);

            const dataWithMeta = {
                ...data,
                synced: this.isOnline,
                localUpdatedAt: new Date().toISOString()
            };

            const request = store.put(dataWithMeta);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Get data from IndexedDB store
     */
    async getFromStore(storeName, key) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.get(key);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Get all data from a store
     */
    async getAllFromStore(storeName) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Delete data from store
     */
    async deleteFromStore(storeName, key) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.delete(key);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Add operation to sync queue (for offline operations)
     */
    async addToSyncQueue(operation) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([STORES.SYNC_QUEUE], 'readwrite');
            const store = transaction.objectStore(STORES.SYNC_QUEUE);

            const queueItem = {
                ...operation,
                timestamp: new Date().toISOString(),
                status: 'pending',
                retryCount: 0
            };

            const request = store.add(queueItem);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Get pending sync operations
     */
    async getPendingSyncOperations() {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([STORES.SYNC_QUEUE], 'readonly');
            const store = transaction.objectStore(STORES.SYNC_QUEUE);
            const index = store.index('status');
            const request = index.getAll('pending');

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Mark sync operation as completed
     */
    async markSyncCompleted(id) {
        return this.deleteFromStore(STORES.SYNC_QUEUE, id);
    }

    /**
     * Sync all pending operations when online
     */
    async syncPendingOperations() {
        if (!this.isOnline || this.syncInProgress) {
            return;
        }

        this.syncInProgress = true;
        this.emit('syncStart');

        try {
            const pendingOps = await this.getPendingSyncOperations();
            let successCount = 0;
            let failCount = 0;

            for (const op of pendingOps) {
                try {
                    await this.executeSyncOperation(op);
                    await this.markSyncCompleted(op.id);
                    successCount++;
                } catch (error) {
                    console.error('Sync operation failed:', error);
                    failCount++;
                }
            }

            if (successCount > 0) {
                this.showNotification(`${successCount} data berhasil disinkronkan`, 'success');
            }

            this.emit('syncComplete', { success: successCount, failed: failCount });
        } catch (error) {
            console.error('Sync failed:', error);
            this.emit('syncError', error);
        } finally {
            this.syncInProgress = false;
        }
    }

    /**
     * Execute a single sync operation
     */
    async executeSyncOperation(operation) {
        const { type, method, endpoint, data } = operation;

        const response = await fetch(endpoint, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: method !== 'GET' ? JSON.stringify(data) : undefined
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    }

    /**
     * Fetch data from API and cache locally
     */
    async fetchAndCache(endpoint, storeName) {
        try {
            if (this.isOnline) {
                const response = await fetch(endpoint, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();

                    // Cache the data
                    const items = data.data || data;
                    for (const item of items) {
                        await this.saveToStore(storeName, { ...item, synced: true });
                    }

                    // Update cache metadata
                    await this.saveToStore(STORES.CACHE_META, {
                        key: storeName,
                        lastUpdated: new Date().toISOString()
                    });

                    return items;
                }
            }

            // Return cached data if offline or request failed
            return await this.getAllFromStore(storeName);
        } catch (error) {
            console.error('Fetch and cache error:', error);
            return await this.getAllFromStore(storeName);
        }
    }

    /**
     * Show notification toast
     */
    showNotification(message, type = 'info') {
        const toast = document.getElementById('toast-container');
        if (!toast) return;

        const alertClass = {
            success: 'alert-success',
            error: 'alert-error',
            warning: 'alert-warning',
            info: 'alert-info'
        }[type] || 'alert-info';

        const notification = document.createElement('div');
        notification.className = `alert ${alertClass}`;
        notification.innerHTML = `<span>${message}</span>`;

        toast.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    /**
     * Event emitter methods
     */
    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }

    off(event, callback) {
        if (this.listeners.has(event)) {
            const callbacks = this.listeners.get(event);
            const index = callbacks.indexOf(callback);
            if (index > -1) {
                callbacks.splice(index, 1);
            }
        }
    }

    emit(event, data) {
        if (this.listeners.has(event)) {
            this.listeners.get(event).forEach(callback => callback(data));
        }
    }
}

// Export stores constant for reference
export { STORES };

// Create and export singleton instance
const offlineSync = new OfflineSync();
export default offlineSync;

// Make it available globally for non-module scripts
window.OfflineSync = offlineSync;
