@extends('layouts.dashboard')
@section('title', 'Site Content — LMS')

@php use Illuminate\Support\Js; @endphp

@section('content')
<div class="max-w-3xl space-y-6"
    x-data="{
        tab: 'home',
        content: {{ Js::from($content) }},
        text: {{ Js::from($textValues) }},
        saving: false, msg: null,
        grads: ['grad-primary','grad-purple','grad-sunset','grad-teal','grad-ph','grad-magenta'],
        pastels: ['pastel-purple','pastel-blue','pastel-green','pastel-pink','pastel-amber','pastel-cyan'],
        async save() {
            this.saving = true; this.msg = null;
            const csrf = document.querySelector('meta[name=csrf-token]').content;
            try {
                if (this.tab === 'text') {
                    const res = await fetch('/dashboard/content/text', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ text: this.text }) });
                    if (!res.ok) throw 0;
                    this.msg = 'Text saved ✔ — refresh a public page to see it.';
                } else {
                    const res = await fetch('/dashboard/content', { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ content: this.content }) });
                    if (!res.ok) throw 0;
                    this.content = (await res.json()).content;
                    this.msg = 'Content saved ✔ — refresh a public page to see it.';
                }
            } catch { this.msg = 'Save failed.'; } finally { this.saving = false; }
        }
    }">
    <div>
        <h1 class="text-3xl">Site Content</h1>
        <p class="mt-1 text-[var(--muted)]">Edit the marketing content on your public pages. Changes go live on save.</p>
    </div>

    @unless ($canManage)
        <p class="text-sm font-bold text-[var(--warning)]">You can view content but need <code>settings.manage</code> to change it.</p>
    @endunless

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-2">
        @foreach (['home' => 'Home', 'about' => 'About', 'pricing' => 'Pricing', 'instructors' => 'Instructors', 'contact' => 'Contact', 'footer' => 'Footer', 'text' => 'Text (EN/BN)'] as $t => $label)
            <button type="button" @click="tab = '{{ $t }}'"
                :class="tab === '{{ $t }}' ? 'bg-[var(--primary)] text-white' : 'bg-[var(--surface)] text-[var(--foreground)] border border-[var(--border)] hover:border-[var(--primary)]'"
                class="rounded-[var(--radius-sm)] px-4 py-2 text-sm font-bold transition-colors">{{ $label }}</button>
        @endforeach
    </div>

    <fieldset @if (! $canManage) disabled @endif class="space-y-6 disabled:opacity-70">
        {{-- ============ HOME ============ --}}
        <div x-show="tab === 'home'" class="space-y-6">
            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg">Tech strip</h3>
                    <button type="button" @click="content.home.techStrip.push('')" class="btn-ghost !py-1.5 !px-3 text-sm">+ Add</button>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <template x-for="(t, i) in content.home.techStrip" :key="i">
                        <div class="flex items-center gap-1 rounded-[var(--radius-sm)] border border-[var(--border)] bg-[var(--background)] p-1.5">
                            <input class="input !py-1.5 !px-2 max-w-[130px] text-sm" x-model="content.home.techStrip[i]">
                            <button type="button" @click="content.home.techStrip.splice(i, 1)" class="px-1.5 text-[var(--danger)]">✕</button>
                        </div>
                    </template>
                </div>
            </div>

            <x-content-list title="Success stats" list="content.home.stats" blank="{ v: '', l: { en: '', bn: '' } }">
                <x-content-field label="Value" model="row.v" />
                <x-content-loc label="Label" model="row.l" />
            </x-content-list>

            <x-content-list title="Technology grid" list="content.home.stack" blank="{ icon: '⭐', name: '', d: { en: '', bn: '' }, bg: 'pastel-blue' }">
                <x-content-field label="Icon" model="row.icon" />
                <x-content-field label="Name" model="row.name" />
                <x-content-loc label="Description" model="row.d" textarea />
                <x-content-select label="Card colour" model="row.bg" options="pastels" />
            </x-content-list>

            <x-content-list title="Success stories" list="content.home.stories" blank="{ name: '', role: { en: '', bn: '' }, grad: 'grad-primary', text: { en: '', bn: '' } }">
                <x-content-field label="Name" model="row.name" />
                <x-content-loc label="Role" model="row.role" />
                <x-content-select label="Avatar colour" model="row.grad" options="grads" />
                <x-content-loc label="Quote" model="row.text" textarea />
            </x-content-list>
        </div>

        {{-- ============ ABOUT ============ --}}
        <div x-show="tab === 'about'" x-cloak class="space-y-6">
            <div class="card space-y-3 p-6">
                <h3 class="text-lg">Hero</h3>
                <x-content-loc label="Title (normal)" model="content.about.hero.titleA" />
                <x-content-loc label="Title (highlighted)" model="content.about.hero.titleHl" />
                <x-content-loc label="Subtitle" model="content.about.hero.subtitle" textarea />
            </div>
            <x-content-list title="Values" list="content.about.values" blank="{ icon: '⭐', title: { en: '', bn: '' }, text: { en: '', bn: '' }, grad: 'grad-primary' }">
                <x-content-field label="Icon" model="row.icon" />
                <x-content-loc label="Title" model="row.title" />
                <x-content-loc label="Text" model="row.text" textarea />
                <x-content-select label="Colour" model="row.grad" options="grads" />
            </x-content-list>
            <x-content-list title="Steps" list="content.about.steps" blank="{ n: '1', title: { en: '', bn: '' }, text: { en: '', bn: '' } }">
                <x-content-field label="Number" model="row.n" />
                <x-content-loc label="Title" model="row.title" />
                <x-content-loc label="Text" model="row.text" textarea />
            </x-content-list>
            <x-content-list title="Stats band" list="content.about.stats" blank="{ v: '', l: { en: '', bn: '' } }">
                <x-content-field label="Value" model="row.v" />
                <x-content-loc label="Label" model="row.l" />
            </x-content-list>
        </div>

        {{-- ============ PRICING ============ --}}
        <div x-show="tab === 'pricing'" x-cloak class="space-y-6">
            <div class="card space-y-3 p-6">
                <h3 class="text-lg">Hero</h3>
                <x-content-loc label="Title (normal)" model="content.pricing.hero.titleA" />
                <x-content-loc label="Title (highlighted)" model="content.pricing.hero.titleHl" />
                <x-content-loc label="Subtitle" model="content.pricing.hero.subtitle" textarea />
            </div>
            <div class="card p-6">
                <x-content-loc label="Footnote (below the plans)" model="content.pricing.footnote" textarea />
                <p class="mt-2 text-xs text-[var(--muted)]">The plans themselves come from Platform → Plans.</p>
            </div>
        </div>

        {{-- ============ INSTRUCTORS ============ --}}
        <div x-show="tab === 'instructors'" x-cloak class="space-y-6">
            <div class="card space-y-3 p-6">
                <h3 class="text-lg">Hero</h3>
                <x-content-loc label="Title (normal)" model="content.instructors.hero.titleA" />
                <x-content-loc label="Title (highlighted)" model="content.instructors.hero.titleHl" />
                <x-content-loc label="Subtitle" model="content.instructors.hero.subtitle" textarea />
            </div>
            <div class="card space-y-3 p-6">
                <h3 class="text-lg">“Become an instructor” banner</h3>
                <x-content-loc label="Title" model="content.instructors.cta.title" />
                <x-content-loc label="Button text" model="content.instructors.cta.button" />
                <x-content-loc label="Text" model="content.instructors.cta.text" textarea />
            </div>
        </div>

        {{-- ============ CONTACT ============ --}}
        <div x-show="tab === 'contact'" x-cloak class="space-y-6">
            <div class="card space-y-3 p-6">
                <h3 class="text-lg">Hero</h3>
                <x-content-loc label="Title (normal)" model="content.contact.hero.titleA" />
                <x-content-loc label="Title (highlighted)" model="content.contact.hero.titleHl" />
                <x-content-loc label="Subtitle" model="content.contact.hero.subtitle" textarea />
            </div>
            <x-content-list title="Contact channels" list="content.contact.channels" blank="{ icon: '✉️', title: { en: '', bn: '' }, value: { en: '', bn: '' }, grad: 'grad-primary' }">
                <x-content-field label="Icon" model="row.icon" />
                <x-content-loc label="Title" model="row.title" />
                <x-content-loc label="Value" model="row.value" />
                <x-content-select label="Colour" model="row.grad" options="grads" />
            </x-content-list>
        </div>

        {{-- ============ FOOTER ============ --}}
        <div x-show="tab === 'footer'" x-cloak class="space-y-6">
            <div class="card space-y-3 p-6">
                <h3 class="text-lg">Footer</h3>
                <x-content-field label="Brand name" model="content.footer.brand" />
                <x-content-loc label="Tagline" model="content.footer.tagline" textarea />
                <p class="text-xs text-[var(--muted)]">Footer link columns come from the site’s page routes.</p>
            </div>
        </div>

        {{-- ============ TEXT (bilingual overrides) ============ --}}
        <div x-show="tab === 'text'" x-cloak class="space-y-6">
            <p class="text-sm text-[var(--muted)]">Homepage headline, sections and CTAs — edit English and Bangla.</p>
            @foreach ($groups as $groupTitle => $keys)
                <div class="card p-6">
                    <h3 class="text-lg">{{ $groupTitle }}</h3>
                    <div class="mt-4 space-y-4">
                        @foreach ($keys as $key => $label)
                            <div>
                                <label class="label">{{ $label }}</label>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <input class="input" placeholder="English" x-model="text['{{ $key }}'].en">
                                    <input class="input" placeholder="বাংলা" x-model="text['{{ $key }}'].bn">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </fieldset>

    @if ($canManage)
        <div class="sticky bottom-4 flex items-center gap-3 rounded-[var(--radius)] border border-[var(--border)] bg-[var(--surface)]/95 p-4 shadow-[var(--shadow-card)] backdrop-blur">
            <button type="button" @click="save()" :disabled="saving" class="btn-primary disabled:opacity-60">
                <span x-show="!saving" x-text="tab === 'text' ? 'Save text' : 'Save all content'"></span>
                <span x-show="saving" x-cloak>Saving…</span>
            </button>
            <span x-show="msg" x-text="msg" class="text-sm font-bold text-[var(--success)]"></span>
        </div>
    @endif
</div>
@endsection
