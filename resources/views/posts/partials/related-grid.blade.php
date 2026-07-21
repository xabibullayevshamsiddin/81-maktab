@php
  $likedPostIds = $likedPostIds ?? collect();
  $bookmarkedPostIds = $bookmarkedPostIds ?? collect();
@endphp

<div class="post-grid related-posts-grid">
  @foreach($relatedPosts as $post)
    @include('posts.partials.post-card', [
      'post' => $post,
      'likedPostIds' => $likedPostIds,
      'bookmarkedPostIds' => $bookmarkedPostIds,
      'detailsBtnClass' => 'btn btn-sm',
      'detailsBtnStyle' => 'margin: 0 16px 16px;',
    ])
  @endforeach
</div>
