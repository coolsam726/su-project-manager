@props(['status'])

<div class="md:w-[24rem] first:ml-8 last:mr-8 flex-shrink-0 mb-5 md:min-h-full flex flex-col rounded-xl bg-gray-200 dark:bg-gray-800">
    @include(static::$headerView)

    <div
        data-status-id="{{ $status['id'] }}"
        class="flex flex-col gap-2 p-3 flex-1 mr-2 overflow-y-auto h-full rounded-xl"
    >
        @foreach($status['records'] as $record)
            @include(static::$recordView)
        @endforeach
    </div>
    <div class="min-h-2"></div>
</div>
