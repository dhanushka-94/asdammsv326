<div id="confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" aria-hidden="true">
    <div id="confirm-modal-backdrop" class="absolute inset-0 bg-brand-blue-dark/50 backdrop-blur-sm"></div>

    <div role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title" class="relative z-10 w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-orange-soft text-brand-orange">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>

        <h2 id="confirm-modal-title" class="font-display text-lg font-bold text-ink">Please confirm</h2>
        <p id="confirm-modal-message" class="mt-2 text-sm leading-relaxed text-muted">Are you sure?</p>

        <div id="confirm-math-wrap" class="mt-4 hidden">
            <label for="confirm-math-input" id="confirm-math-label" class="form-label">Solve: 0 + 0 = ?</label>
            <input id="confirm-math-input" type="number" inputmode="numeric" class="form-input" placeholder="Answer" autocomplete="off">
            <p id="confirm-math-error" class="mt-1.5 hidden text-xs font-semibold text-red-600">Incorrect answer. Try again.</p>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" id="confirm-modal-no" class="btn-outline">
                No
            </button>
            <button type="button" id="confirm-modal-yes" class="btn-primary">
                Yes
            </button>
        </div>
    </div>
</div>
