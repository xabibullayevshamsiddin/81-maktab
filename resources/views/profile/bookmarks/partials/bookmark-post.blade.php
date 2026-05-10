@include('posts.partials.post-card', [
    'post' => $bookmark->bookmarkable,
    'likedPostIds' => $likedPostIds,
    'bookmarkedPostIds' => $bookmarkedPostIds,
])
