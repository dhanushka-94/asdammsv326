<div id="profile-crop-modal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4" aria-hidden="true">
    <div data-profile-crop-backdrop class="absolute inset-0 bg-brand-blue-dark/55 backdrop-blur-sm"></div>

    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="profile-crop-title"
        class="relative z-10 flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
    >
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 id="profile-crop-title" class="font-display text-lg font-bold text-ink">Crop profile picture</h2>
            <p class="mt-1 text-sm text-muted">Locked to a square (1:1). Drag to position, then apply.</p>
        </div>

        <div class="min-h-0 flex-1 overflow-auto bg-slate-950/95 p-4">
            <div class="mx-auto max-h-[55vh] max-w-full">
                <img id="profile-crop-image" src="" alt="Crop source" class="block max-w-full">
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn-outline" data-profile-crop-cancel>Cancel</button>
            <button type="button" class="btn-primary" data-profile-crop-apply>Apply crop</button>
        </div>
    </div>
</div>
