@extends('layouts.public')
@section('title', 'Donate to Case #' . $crisis->crisis_id)

@section('content')
<section class="container py-4">
    <a href="{{ route('crisis.show', $crisis->crisis_id) }}" class="btn btn-link p-0 mb-3"><i class="bi bi-arrow-left"></i> Back to case details</a>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="content-card">
                <h3 class="mb-3"><i class="bi bi-heart-fill text-danger"></i> Make a Donation</h3>
                <p class="text-muted">Your contribution will be recorded on the audit chain and a receipt will be emailed to you.</p>

                <form method="POST" action="{{ route('donate.store', $crisis->crisis_id) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Donation Amount (RM)</label>
                        <div class="amount-presets mb-2">
                            @foreach([50, 100, 250, 500, 1000] as $amt)
                                <button type="button" class="btn btn-outline-secondary btn-sm preset-amount" data-amount="{{ $amt }}">RM {{ $amt }}</button>
                            @endforeach
                        </div>
                        <input type="number" name="donation_amount" id="donation_amount" class="form-control form-control-lg @error('donation_amount') is-invalid @enderror" min="1" max="1000000" value="{{ old('donation_amount', 100) }}" required>
                        @error('donation_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="donor_name" class="form-control @error('donor_name') is-invalid @enderror" value="{{ old('donor_name') }}" required>
                            @error('donor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="donor_email" class="form-control @error('donor_email') is-invalid @enderror" value="{{ old('donor_email') }}" required>
                            @error('donor_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            <option value="FPX" {{ old('payment_method')==='FPX'?'selected':'' }}>FPX (Online Banking)</option>
                            <option value="credit_card" {{ old('payment_method')==='credit_card'?'selected':'' }}>Credit / Debit Card</option>
                            <option value="wallet" {{ old('payment_method')==='wallet'?'selected':'' }}>e-Wallet</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Support Message (optional)</label>
                        <textarea name="support_message" rows="2" maxlength="1000" class="form-control" placeholder="A short note of support…">{{ old('support_message') }}</textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="anonymous" value="1" id="anon" class="form-check-input">
                        <label for="anon" class="form-check-label small">Donate anonymously (your name will appear as "Anonymous Donor")</label>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-shield-check"></i> Confirm Donation
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="content-card">
                <h6 class="text-uppercase text-muted small mb-3">You are supporting</h6>
                <h5>{{ ucwords(str_replace('_', ' ', $crisis->crisis_type)) }}</h5>
                @if($crisis->location)
                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> {{ $crisis->location }}</p>
                @endif
                <p>{{ \Illuminate\Support\Str::limit($crisis->crisis_description, 240) }}</p>
                <hr>
                <x-donation-progress :crisis="$crisis" />
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.querySelectorAll('.preset-amount').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('donation_amount').value = this.dataset.amount;
    });
});
</script>
@endpush
@endsection
