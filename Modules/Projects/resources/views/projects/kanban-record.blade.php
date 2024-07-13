<div
        id="{{ $record->getKey() }}"
        wire:click="recordClicked('{{ $record->getKey() }}', {{ @json_encode($record) }})"
        @class([
            'record bg-white dark:bg-gray-700 rounded-lg px-4 py-2 cursor-grab text-gray-600 dark:text-gray-200',
            '!bg-success-50 border border-success-500 dark:border-success-400' => $record->status ==='DONE',
            '!bg-warning-50 border border-warning-500 dark:border-warning-400' => $record->status ==='IN_PROGRESS',
            '!bg-danger-50 border border-danger-500 dark:border-danger-400' => $record->status ==='TODO',

        ])
        @if($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}) < 3)
            x-data
        x-init="
            $el.classList.add('animate-pulse-twice', 'bg-primary-100', 'dark:bg-primary-800')
            $el.classList.remove('bg-white', 'dark:bg-gray-700')
            setTimeout(() => {
                $el.classList.remove('bg-primary-100', 'dark:bg-primary-800')
                $el.classList.add('bg-white', 'dark:bg-gray-700')
            }, 3000)
        "
        @endif
>
    <div class="flex justify-between">
        <div class="font-semibold flex-1">{{ $record->{static::$recordTitleAttribute} }}</div>
        <div class="flex items center">
            @if($record->status === 'DONE')
                <svg class="size-8" viewBox="0 0 24.00 24.00" fill="none" xmlns="http://www.w3.org/2000/svg"
                     stroke="#20cf35">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#20cf35"
                       stroke-width="1.2"></g>
                    <g id="SVGRepo_iconCarrier">
                        <circle cx="10" cy="14" r="7" fill="#2A4157" fill-opacity="0.24"></circle>
                        <path d="M6 13L9.21391 15.4104C9.65027 15.7377 10.2684 15.6549 10.6033 15.2244L17 7"
                              stroke="#20cf35" stroke-width="2.4" stroke-linecap="round"></path>
                    </g>
                </svg>
            @elseif($record->status === 'IN_PROGRESS')
                <svg class="size-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC"
                       stroke-width="0.384"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path d="M12 3.3C12 3.15765 12 3.08648 12.0457 3.04226C12.0914 2.99805 12.1609 3.00036 12.3 3.005C14.174 3.06747 15.9846 3.71402 17.4765 4.85803C19.0479 6.06299 20.178 7.75256 20.6918 9.66496C21.2056 11.5773 21.0743 13.6058 20.3183 15.436C19.5623 17.2662 18.2238 18.796 16.5102 19.7883C14.7966 20.7807 12.8035 21.1802 10.8398 20.9249C8.87614 20.6696 7.05146 19.7739 5.64851 18.3764C4.24556 16.9789 3.34265 15.1578 3.0797 13.1951C2.83005 11.3317 3.17049 9.43952 4.04907 7.78303C4.11426 7.66014 4.14685 7.59869 4.20795 7.58105C4.26906 7.56341 4.33079 7.59883 4.45425 7.66968L10.0014 10.853C10.1193 10.9207 10.1782 10.9545 10.1967 11.007C10.2153 11.0595 10.1876 11.1306 10.1322 11.2727C10.0099 11.587 9.96817 11.9287 10.0134 12.2662C10.0719 12.7033 10.273 13.1088 10.5855 13.4201C10.8979 13.7313 11.3043 13.9308 11.7416 13.9876C12.1789 14.0445 12.6228 13.9555 13.0044 13.7345C13.3861 13.5135 13.6842 13.1728 13.8525 12.7652C14.0209 12.3576 14.0501 11.9059 13.9357 11.48C13.8213 11.0541 13.5696 10.6778 13.2197 10.4094C12.9495 10.2023 12.6324 10.0683 12.2989 10.018C12.148 9.99529 12.0726 9.98392 12.0363 9.94173C12 9.89955 12 9.83158 12 9.69564V3.3Z"
                              fill="#f8a649" fill-opacity="0.24"></path>
                        <path d="M8.65 17.8024C8.5787 17.9259 8.54306 17.9876 8.56095 18.0491C8.57884 18.1105 8.64021 18.1426 8.76294 18.2066C9.62424 18.6558 10.5707 18.9213 11.5422 18.985C12.6136 19.0552 13.6868 18.878 14.6788 18.4672C15.6708 18.0563 16.5549 17.4227 17.2629 16.6154C17.9708 15.8082 18.4836 14.8489 18.7615 13.8117C19.0394 12.7746 19.075 11.6874 18.8655 10.6344C18.656 9.58128 18.2071 8.5905 17.5535 7.73867C16.8998 6.88684 16.059 6.19678 15.096 5.72189C14.2228 5.29128 13.2704 5.04804 12.2999 5.00643C12.1616 5.0005 12.0925 4.99753 12.0462 5.04182C12 5.08611 12 5.15741 12 5.3V9.70234C12 9.83826 12 9.90622 12.0363 9.9484C12.0726 9.99057 12.148 10.002 12.2989 10.0248C12.5015 10.0555 12.6987 10.1172 12.8835 10.2083C13.1584 10.3439 13.3983 10.5408 13.5849 10.7839C13.7714 11.027 13.8995 11.3097 13.9593 11.6103C14.0191 11.9108 14.0089 12.2211 13.9296 12.517C13.8503 12.813 13.704 13.0868 13.5019 13.3172C13.2999 13.5475 13.0476 13.7283 12.7645 13.8456C12.4814 13.9629 12.1751 14.0134 11.8693 13.9934C11.6637 13.9799 11.462 13.9347 11.2712 13.86C11.1292 13.8043 11.0581 13.7765 11.0056 13.7949C10.9531 13.8133 10.9191 13.8721 10.8512 13.9898L8.65 17.8024Z"
                              fill="#f8a649"></path>
                    </g>
                </svg>
            @elseif($record->status === 'TODO')
                <svg class="size-8" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"
                     fill="#000000">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools --> <title>
                            ic_fluent_shifts_pending_24_regular</title>
                        <desc>Created with Sketch.</desc>
                        <g id="🔍-Product-Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g id="ic_fluent_shifts_pending_24_regular" fill="#d72d2d" fill-rule="nonzero">
                                <path d="M6.5,12 C9.53756612,12 12,14.4624339 12,17.5 C12,20.5375661 9.53756612,23 6.5,23 C3.46243388,23 1,20.5375661 1,17.5 C1,14.4624339 3.46243388,12 6.5,12 Z M6.5,19.88 C6.15509206,19.88 5.87548893,20.1596031 5.87548893,20.5045111 C5.87548893,20.849419 6.15509206,21.1290221 6.5,21.1290221 C6.84490794,21.1290221 7.12451107,20.849419 7.12451107,20.5045111 C7.12451107,20.1596031 6.84490794,19.88 6.5,19.88 Z M17.75,3 C19.5449254,3 21,4.45507456 21,6.25 L21,17.75 C21,19.5449254 19.5449254,21 17.75,21 L11.9774077,21.0012092 C12.2742498,20.5377831 12.5138894,20.0341997 12.6863646,19.5004209 L17.75,19.5 C18.7164983,19.5 19.5,18.7164983 19.5,17.75 L19.5,6.25 C19.5,5.28350169 18.7164983,4.5 17.75,4.5 L6.25,4.5 C5.28350169,4.5 4.5,5.28350169 4.5,6.25 L4.49957906,11.3136354 C3.96580034,11.4861106 3.46221691,11.7257502 2.99879075,12.0225923 L3,6.25 C3,4.45507456 4.45507456,3 6.25,3 L17.75,3 Z M6.5000438,14.0030924 C5.45209485,14.0030924 4.63575024,14.8204841 4.64666418,15.9573825 C4.64931495,16.2335122 4.87531114,16.4552106 5.15144079,16.4525598 C5.42757044,16.449909 5.64926888,16.2239129 5.6466181,15.9477832 C5.64105975,15.3687734 6.00627225,15.0030924 6.5000438,15.0030924 C6.97241724,15.0030924 7.35344646,15.3949794 7.35344646,15.9525829 C7.35344646,16.1768805 7.27815856,16.343747 7.03551615,16.6299729 L6.93650069,16.7432479 L6.67112833,17.0333231 C6.18682267,17.5748716 6.0000438,17.9254825 6.0000438,18.5006005 C6.0000438,18.7767429 6.22390142,19.0006005 6.5000438,19.0006005 C6.77618617,19.0006005 7.0000438,18.7767429 7.0000438,18.5006005 C7.0000438,18.268353 7.07645293,18.0980788 7.32379001,17.8062547 L7.42473827,17.6907646 L7.69048308,17.400276 C8.16815154,16.8660369 8.35344646,16.5185919 8.35344646,15.9525829 C8.35344646,14.8488849 7.5310877,14.0030924 6.5000438,14.0030924 Z M11.75,6 C12.1296958,6 12.443491,6.28215388 12.4931534,6.64822944 L12.5,6.75 L12.5,12 L16.2482627,12 C16.6624763,12 16.9982627,12.3357864 16.9982627,12.75 C16.9982627,13.1296958 16.7161089,13.443491 16.3500333,13.4931534 L16.2482627,13.5 L11.75,13.5 C11.3703042,13.5 11.056509,13.2178461 11.0068466,12.8517706 L11,12.75 L11,6.75 C11,6.33578644 11.3357864,6 11.75,6 Z"
                                      id="🎨-Color"></path>
                            </g>
                        </g>
                    </g>
                </svg>
            @endif
        </div>
    </div>
    <div class="text-xs font-light text-gray-500">{{$record->department?->name ?? 'No Department'}}</div>
    <div class="mb-1 text-xs text-gray-500 font-medium dark:text-gray-100">{{$record->progress}}%</div>
    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-4 dark:bg-gray-700">
        <div @class([
            'h-1.5 rounded-full',
            'bg-danger-500 dark:bg-danger-400' => $record->progress < 30,
            'bg-warning-500 dark:bg-warning-400' => $record->progress >= 30 && $record->progress < 100,
            'bg-success-500 dark:bg-success-400'  => $record->progress >= 100,
            ])
             style="width: {{$record->progress ?: 0}}%"></div>
    </div>
    <div class="flex items-center justify-between">
        <div></div>
        <div class="text-xs">{{$taskCount = $record->tasks->count()}} {{str('task')->plural($taskCount)}}</div>
    </div>
</div>
