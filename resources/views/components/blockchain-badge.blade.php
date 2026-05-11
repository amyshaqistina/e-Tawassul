{{--
    Blockchain verification badge — green pill + truncated hash with copy.
    Expected props: $hash (string|null), $label = "Verified"
--}}
@props(['hash' => null, 'label' => 'Blockchain Verified'])

@if($hash)
    <div class="blockchain-badge"
         x-data="{ copied: false, copy() { navigator.clipboard.writeText('{{ $hash }}'); this.copied = true; setTimeout(() => this.copied = false, 1500); } }">
        <span class="blockchain-pill">
            <i class="bi bi-shield-check"></i> {{ $label }}
        </span>
        <code class="blockchain-hash" title="{{ $hash }}">{{ substr($hash, 0, 12) }}…{{ substr($hash, -8) }}</code>
        <button type="button" class="btn btn-link btn-sm p-0 blockchain-copy" @click="copy()">
            <i class="bi" :class="copied ? 'bi-check-lg text-success' : 'bi-clipboard'"></i>
            <span class="small" x-text="copied ? 'Copied!' : 'Copy'"></span>
        </button>
    </div>
@else
    <span class="blockchain-pill blockchain-pill-pending">
        <i class="bi bi-hourglass-split"></i> Not yet recorded
    </span>
@endif
