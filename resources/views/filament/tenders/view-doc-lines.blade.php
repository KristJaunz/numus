<!-- resources/views/filament/tenders/view-doc-lines.blade.php -->

<div>

    <table class="w-full table-auto border-collapse">
        <thead>
        <tr class="bg-gray-500">
            <th class="px-4 py-2 border-b text-left text-sm font-semibold text-gray-200">Prece</th>
            <th class="px-4 py-2 border-b text-left text-sm font-semibold text-gray-200">Daudzums</th>
            <th class="px-4 py-2 border-b text-left text-sm font-semibold text-gray-200">PVN</th>
            <th class="px-4 py-2 border-b text-left text-sm font-semibold text-gray-200">Cena</th>
            <th class="px-4 py-2 border-b text-left text-sm font-semibold text-gray-200">Atlaide</th>
            <th class="px-4 py-2 border-b text-left text-sm font-semibold text-gray-200">Gala cena</th>
        </tr>
        </thead>
        <tbody>

        @php

        $total = 0;

        @endphp

        @foreach ($tender->docLines as $record)
            <tr class="">
                <td class="px-4 py-2 border-b text-sm text-gray-200">{{ \App\Models\Jumis\Product::list()[$record->i] ?? $record->i }}</td>
                <td class="px-4 py-2 border-b text-sm text-gray-200">{{ $record->q }}</td>
                <td class="px-4 py-2 border-b text-sm text-gray-200">{{ $record->r }}</td>
                <td class="px-4 py-2 border-b text-sm text-gray-200">{{ $record->pb }}</td>
                <td class="px-4 py-2 border-b text-sm text-gray-200">{{ $record->d }}</td>
                <td class="px-4 py-2 border-b text-sm text-gray-200">{{ $record->p }}</td>
            </tr>
            @php
                $total += $record->p * $record->q;
            @endphp
        @endforeach
            <tr class="">
                <td class="px-4 py-2 border-b text-sm text-gray-200"></td>
                <td class="px-4 py-2 border-b text-sm text-gray-200"></td>
                <td class="px-4 py-2 border-b text-sm text-gray-200"></td>
                <td class="px-4 py-2 border-b text-sm text-gray-200"></td>
                <td class="px-4 py-2 border-b text-sm text-gray-200 "></td>
                <td class="px-4 py-2 border-b text-sm text-gray-200">Apmaksai: {{ $total }}</td>
            </tr>
        </tbody>
    </table>
</div>
