<h2>New Review Submitted</h2>

<p><strong>Name:</strong> {{ $review->name }}</p>
<p><strong>Email:</strong> {{ $review->email }}</p>
<p><strong>Rating:</strong> {{ $review->rating }}/5</p>

<p><strong>Comment:</strong></p>
<p>{{ $review->comment }}</p>

<hr>

<p>
    Source: {{ $review->source }}
</p>

<p>
    Source ID: {{ $review->source_id }}
</p>

<p>
    Date: {{ $review->created_at }}
</p>
