<div>
    <input type="text" wire:model="url" placeholder="Enter URL">
    <button wire:click="getAllLinks">Extract Links</button>
    @if ($errorMessage)
        <div class="error-message">{{ $errorMessage }}</div>
    @endif

    <div v-if="{{ count($allLinks) > 0 }}">
        <div>
            Number of links found: {{ $linkCount }}
        </div>
        Links found on {{ $url }}:
        <ul>
            @foreach ($allLinks as $link)
                <li>{{ $link }}</li>
            @endforeach
        </ul>
        <label for="searchWord">Search Word:</label>
        <input type="text" wire:model="searchWord" id="searchWord" placeholder="Enter word to search">
        <button wire:click="searchForWord">Search Links</button>
         {{-- <div>
            Total word count: {{ $totalWordCount }}
        </div> --}}
        @if ($wordCounts)
            <ul>
                @foreach ($wordCounts as $link => $count)
                    <li>{{ $link }} - <strong>Word count: {{ $count }}</strong></li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
