import './bootstrap';
import offlineSync from './offline-sync';
import { Calendar } from 'fullcalendar'

// Initialize offline sync to make it available globally if needed
window.offlineSync = offlineSync;

// Register PWA Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/build/sw.js', { scope: '/' })
            .then(registration => {
                console.log('SW registered: ', registration);
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}



