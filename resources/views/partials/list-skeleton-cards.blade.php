<div class="list-skeleton-grid" data-list-skeleton aria-hidden="true">
  @for ($i = 0; $i < ($count ?? 6); $i++)
    <article class="list-skeleton-card">
      <div class="skeleton-loader skeleton-card"></div>
      <div class="skeleton-loader skeleton-line-lg"></div>
      <div class="skeleton-loader skeleton-line-md"></div>
      <div class="skeleton-loader skeleton-line-sm"></div>
    </article>
  @endfor
</div>
