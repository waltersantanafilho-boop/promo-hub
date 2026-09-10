@props(['id', 'label', 'count', 'icon', 'description', 'tone' => 'blue'])

<article {{ $attributes->class('card admin-stat-card h-100') }} aria-labelledby="{{ $id }}-label">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <h2 class="admin-stat-label" id="{{ $id }}-label">{{ $label }}</h2>
                <p class="admin-stat-value mb-0">{{ number_format($count, 0, ',', '.') }}</p>
            </div>
            <span class="admin-stat-icon admin-tone-{{ $tone }}"><x-admin.icon :name="$icon" /></span>
        </div>
        <p class="admin-stat-description mb-0 mt-3">{{ $description }}</p>
    </div>
</article>
