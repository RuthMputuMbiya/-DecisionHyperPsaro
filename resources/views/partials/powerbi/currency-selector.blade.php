{{-- resources/views/partials/currency-selector.blade.php --}}
<div class="currency-selector">
    <form action="{{ route('change-currency') }}" method="POST" class="d-flex gap-1 m-0">
        @csrf
        <button type="submit" name="devise" value="CDF" 
            class="currency-btn {{ session('devise', 'USD') == 'CDF' ? 'active' : '' }}">
            CDF
        </button>
        <button type="submit" name="devise" value="USD" 
            class="currency-btn {{ session('devise', 'USD') == 'USD' ? 'active' : '' }}">
            USD
        </button>
        <button type="submit" name="devise" value="EUR" 
            class="currency-btn {{ session('devise', 'USD') == 'EUR' ? 'active' : '' }}">
            EUR
        </button>
    </form>
</div>

<style>
.currency-selector {
    display: flex;
    gap: 5px;
    background: #f1f5f9;
    padding: 5px;
    border-radius: 40px;
}
.currency-btn {
    background: none;
    border: none;
    padding: 6px 14px;
    border-radius: 40px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    color: #475569;
}
.currency-btn.active {
    background: #1B6EF3;
    color: white;
}
.currency-btn:hover:not(.active) {
    background: #e2e8f0;
}
</style>