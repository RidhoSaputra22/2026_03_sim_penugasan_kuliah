<x-layouts.app title="Manajemen Event">
    <x-slot:header>
        <x-layouts.page-header title="Manajemen Event" description="Kelola semua event kalender kustom">
            <x-slot:actions>
                <x-ui.button type="primary" size="sm" :href="route('events.create')">Tambah Event</x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Lokasi</th>
                        <th>Warna</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                    <tr>
                        <td>{{ $event->title }}</td>
                        <td>{{ $event->description }}</td>
                        <td>{{ $event->start }}</td>
                        <td>{{ $event->end }}</td>
                        <td>{{ $event->location }}</td>
                        <td><span style="display:inline-block;width:24px;height:24px;background:{{ $event->color ?? '#2196f3' }};border-radius:4px;"></span></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <x-ui.button type="warning" size="sm" :href="route('events.edit', $event)" :isSubmit="false">
                                    Edit
                                </x-ui.button>
                                <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="error" size="sm">Hapus</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>
