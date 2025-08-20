@if($items->hasPages())
    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted">
            Affichage de {{ $items->firstItem() }} à {{ $items->lastItem() }} 
            sur {{ $items->total() }} résultats
        </div>
        
        <nav aria-label="Pagination">
            {{ $items->appends(request()->query())->links('pagination::bootstrap-4') }}
        </nav>
    </div>
@endif