<?php

namespace App\Http\Controllers;

use App\Models\AiInteraction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminAiReviewController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageInbox(), 403);

        $q = trim((string) $request->query('q', ''));
        $kind = (string) $request->query('kind', 'all');

        if (! in_array($kind, ['all', 'unanswered', 'unhelpful', 'support'], true)) {
            $kind = 'all';
        }

        if (! Schema::hasTable('ai_interactions')) {
            $reviews = new LengthAwarePaginator([], 0, 20, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return view('admin.ai_reviews.index', compact('reviews', 'q', 'kind'));
        }

        $selectColumns = ['id', 'question', 'created_at'];
        foreach ([
            'response_text',
            'response_source',
            'response_type',
            'user_role',
            'is_helpful',
            'is_unanswered',
            'support_converted',
            'meta',
        ] as $column) {
            if (Schema::hasColumn('ai_interactions', $column)) {
                $selectColumns[] = $column;
            }
        }

        $query = AiInteraction::query()
            ->where(function ($builder): void {
                $builder->whereNull('is_helpful')
                    ->orWhere('is_helpful', false);
            })
            ->where(function ($builder): void {
                $builder->where('is_unanswered', true)
                    ->orWhere('is_helpful', false)
                    ->orWhere('support_converted', true);
            });

        if ($kind === 'unanswered') {
            $query->where('is_unanswered', true);
        } elseif ($kind === 'unhelpful') {
            $query->where('is_helpful', false);
        } elseif ($kind === 'support') {
            $query->where('support_converted', true);
        }

        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $builder->where('question', 'like', '%'.$q.'%');

                if (Schema::hasColumn('ai_interactions', 'response_text')) {
                    $builder->orWhere('response_text', 'like', '%'.$q.'%');
                }

                if (Schema::hasColumn('ai_interactions', 'response_source')) {
                    $builder->orWhere('response_source', 'like', '%'.$q.'%');
                }

                if (Schema::hasColumn('ai_interactions', 'meta')) {
                    $builder->orWhereRaw('CAST(meta AS CHAR) LIKE ?', ['%'.$q.'%']);
                }
            });
        }

        $reviews = $query
            ->latest('id')
            ->paginate(20, $selectColumns)
            ->withQueryString();

        return view('admin.ai_reviews.index', compact('reviews', 'q', 'kind'));
    }

    public function destroy(Request $request, int $interaction): RedirectResponse
    {
        abort_unless($request->user()->canManageInbox(), 403);

        if (! Schema::hasTable('ai_interactions')) {
            return redirect()
                ->route('admin.ai-reviews.index', $request->only(['q', 'kind']))
                ->with('success', 'AI review yozuvi topilmadi.');
        }

        $record = AiInteraction::query()->whereKey($interaction)->firstOrFail();
        $record->delete();

        return redirect()
            ->route('admin.ai-reviews.index', $request->only(['q', 'kind']))
            ->with('success', 'AI review yozuvi o\'chirildi.');
    }

    public function destroyUnhelpful(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageInbox(), 403);

        if (! Schema::hasTable('ai_interactions')) {
            return redirect()
                ->route('admin.ai-reviews.index', $request->only(['q', 'kind']))
                ->with('success', 'O‘chirish uchun AI review yozuvlari topilmadi.');
        }

        $deleted = AiInteraction::query()
            ->where('is_helpful', false)
            ->delete();

        return redirect()
            ->route('admin.ai-reviews.index', $request->only(['q', 'kind']))
            ->with('success', $deleted > 0
                ? "Foydasiz deb belgilangan {$deleted} ta AI review yozuvi o‘chirildi."
                : 'Foydasiz deb belgilangan AI review yozuvlari topilmadi.'
            );
    }
}
