<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Editable marketing content for the public website (home, about, pricing,
 * instructors, contact). Stored as a single JSON `site_content` setting and
 * deep-merged over the DEFAULTS below, so an admin only needs to override the
 * bits they want to change and everything else keeps its shipped value.
 *
 *  GET  /api/content   — public; returns the effective (merged) content.
 *  PUT  /api/content   — settings.manage; saves overrides.
 */
class SiteContentController extends Controller
{
    /**
     * The shipped defaults — mirror of what the pages used to hard-code.
     * Prose fields are bilingual `['en' => ..., 'bn' => ...]`; styling tokens
     * and identifiers (icon, grad, bg, numbers, names) stay plain strings.
     */
    public const DEFAULTS = [
        'home' => [
            'techStrip' => [
                'JavaScript', 'TypeScript', 'React', 'Next.js', 'Node.js', 'Express',
                'MongoDB', 'PostgreSQL', 'Prisma', 'Tailwind', 'Docker', 'AI',
            ],
            'stats' => [
                ['v' => '75%', 'l' => ['en' => 'Job Placement', 'bn' => 'জব প্লেসমেন্ট']],
                ['v' => '25+', 'l' => ['en' => 'Countries', 'bn' => 'দেশ']],
                ['v' => '230+', 'l' => ['en' => 'Hiring Companies', 'bn' => 'হায়ারিং কোম্পানি']],
                ['v' => '10K+', 'l' => ['en' => 'Happy Students', 'bn' => 'খুশি শিক্ষার্থী']],
            ],
            'stack' => [
                ['icon' => '🟨', 'name' => 'JavaScript', 'd' => ['en' => 'The language of the web — from fundamentals to advanced patterns.', 'bn' => 'ওয়েবের ভাষা — বেসিক থেকে অ্যাডভান্সড প্যাটার্ন পর্যন্ত।'], 'bg' => 'pastel-amber'],
                ['icon' => '🔷', 'name' => 'TypeScript', 'd' => ['en' => 'Type-safe JavaScript for scalable, bug-free applications.', 'bn' => 'স্কেলেবল, বাগ-মুক্ত অ্যাপের জন্য টাইপ-সেফ জাভাস্ক্রিপ্ট।'], 'bg' => 'pastel-blue'],
                ['icon' => '⚛️', 'name' => 'React', 'd' => ['en' => 'Build modern, interactive user interfaces with components.', 'bn' => 'কম্পোনেন্ট দিয়ে আধুনিক, ইন্টারঅ্যাকটিভ ইউজার ইন্টারফেস তৈরি করুন।'], 'bg' => 'pastel-cyan'],
                ['icon' => '▲', 'name' => 'Next.js', 'd' => ['en' => 'Production React framework with routing, SSR and APIs.', 'bn' => 'রাউটিং, SSR ও API সহ প্রোডাকশন React ফ্রেমওয়ার্ক।'], 'bg' => 'pastel-purple'],
                ['icon' => '🟩', 'name' => 'Node & Express', 'd' => ['en' => 'Build fast, scalable backend APIs and services.', 'bn' => 'দ্রুত, স্কেলেবল ব্যাকএন্ড API ও সার্ভিস তৈরি করুন।'], 'bg' => 'pastel-green'],
                ['icon' => '🗄️', 'name' => 'Databases', 'd' => ['en' => 'MongoDB, PostgreSQL & Prisma — model and query real data.', 'bn' => 'MongoDB, PostgreSQL ও Prisma — রিয়েল ডেটা মডেল ও কোয়েরি করুন।'], 'bg' => 'pastel-pink'],
                ['icon' => '🎨', 'name' => 'Tailwind CSS', 'd' => ['en' => 'Design beautiful, responsive UIs quickly.', 'bn' => 'দ্রুত সুন্দর, রেসপন্সিভ UI ডিজাইন করুন।'], 'bg' => 'pastel-cyan'],
                ['icon' => '🤖', 'name' => 'AI Engineering', 'd' => ['en' => 'Build AI-powered features into real applications.', 'bn' => 'রিয়েল অ্যাপে AI-চালিত ফিচার যুক্ত করুন।'], 'bg' => 'pastel-purple'],
            ],
            'stories' => [
                ['name' => 'Rahim Ahmed', 'role' => ['en' => 'Frontend Engineer @ Startup', 'bn' => 'ফ্রন্টএন্ড ইঞ্জিনিয়ার @ স্টার্টআপ'], 'grad' => 'grad-primary', 'text' => ['en' => 'The projects made my portfolio stand out. I got hired within 2 months of finishing.', 'bn' => 'প্রজেক্টগুলো আমার পোর্টফোলিও আলাদা করে তুলেছে। শেষ করার ২ মাসের মধ্যেই চাকরি পেয়েছি।']],
                ['name' => 'Nusrat Jahan', 'role' => ['en' => 'Full-Stack Developer', 'bn' => 'ফুল-স্ট্যাক ডেভেলপার'], 'grad' => 'grad-purple', 'text' => ['en' => 'Live classes and mentor support kept me going. Best decision for my career.', 'bn' => 'লাইভ ক্লাস আর মেন্টর সাপোর্ট আমাকে চালিয়ে যেতে সাহায্য করেছে। ক্যারিয়ারের সেরা সিদ্ধান্ত।']],
                ['name' => 'Tanvir Hasan', 'role' => ['en' => 'Software Engineer', 'bn' => 'সফটওয়্যার ইঞ্জিনিয়ার'], 'grad' => 'grad-sunset', 'text' => ['en' => 'From zero coding to a real job. The curriculum is genuinely industry-standard.', 'bn' => 'শূন্য কোডিং থেকে রিয়েল চাকরি। কারিকুলাম সত্যিই ইন্ডাস্ট্রি-স্ট্যান্ডার্ড।']],
            ],
        ],
        'about' => [
            'hero' => [
                'titleA' => ['en' => 'Learning,', 'bn' => 'শেখা,'],
                'titleHl' => ['en' => 'reimagined', 'bn' => 'নতুনভাবে'],
                'subtitle' => ['en' => 'LMS is a modern platform for creating, selling and teaching courses — with live classes, quizzes, certificates and flexible pricing. Whether you\'re a solo instructor or a growing organization, everything you need is here.', 'bn' => 'LMS হলো কোর্স তৈরি, বিক্রি ও শেখানোর একটি আধুনিক প্ল্যাটফর্ম — লাইভ ক্লাস, কুইজ, সার্টিফিকেট ও নমনীয় প্রাইসিং সহ। আপনি একক ইন্সট্রাক্টর হোন বা বাড়তে থাকা প্রতিষ্ঠান, প্রয়োজনীয় সবকিছু এখানেই।'],
            ],
            'values' => [
                ['icon' => '🎯', 'title' => ['en' => 'Learner-first', 'bn' => 'শিক্ষার্থী-প্রথম'], 'text' => ['en' => 'Every feature is built to help students actually learn and finish.', 'bn' => 'প্রতিটি ফিচার শিক্ষার্থীদের সত্যিকারভাবে শিখতে ও শেষ করতে সাহায্য করার জন্য তৈরি।'], 'grad' => 'grad-primary'],
                ['icon' => '🧩', 'title' => ['en' => 'Only what you need', 'bn' => 'শুধু যা প্রয়োজন'], 'text' => ['en' => 'Turn modules on or off — never pay for features you won\'t use.', 'bn' => 'মডিউল চালু বা বন্ধ করুন — যে ফিচার ব্যবহার করবেন না তার জন্য কখনো পে করবেন না।'], 'grad' => 'grad-purple'],
                ['icon' => '⚡', 'title' => ['en' => 'Fast & modern', 'bn' => 'দ্রুত ও আধুনিক'], 'text' => ['en' => 'Built on Laravel + Next.js for a snappy, reliable experience.', 'bn' => 'Laravel + Next.js-এ তৈরি, স্ন্যাপি ও নির্ভরযোগ্য অভিজ্ঞতার জন্য।'], 'grad' => 'grad-sunset'],
                ['icon' => '🌍', 'title' => ['en' => 'For everyone', 'bn' => 'সবার জন্য'], 'text' => ['en' => 'Students, instructors and organizations — all in one platform.', 'bn' => 'শিক্ষার্থী, ইন্সট্রাক্টর ও প্রতিষ্ঠান — সব এক প্ল্যাটফর্মে।'], 'grad' => 'grad-teal'],
            ],
            'steps' => [
                ['n' => '1', 'title' => ['en' => 'Create your account', 'bn' => 'আপনার অ্যাকাউন্ট তৈরি করুন'], 'text' => ['en' => 'Sign up as a student, instructor or organization in seconds.', 'bn' => 'শিক্ষার্থী, ইন্সট্রাক্টর বা প্রতিষ্ঠান হিসেবে কয়েক সেকেন্ডে সাইন আপ করুন।']],
                ['n' => '2', 'title' => ['en' => 'Build or enroll', 'bn' => 'তৈরি করুন বা এনরোল করুন'], 'text' => ['en' => 'Instructors build courses; students enroll — free or paid.', 'bn' => 'ইন্সট্রাক্টররা কোর্স তৈরি করেন; শিক্ষার্থীরা এনরোল করে — ফ্রি বা পেইড।']],
                ['n' => '3', 'title' => ['en' => 'Learn & grow', 'bn' => 'শিখুন ও এগিয়ে যান'], 'text' => ['en' => 'Take lessons, join live classes, pass quizzes, earn certificates.', 'bn' => 'লেসন নিন, লাইভ ক্লাসে যোগ দিন, কুইজ পাস করুন, সার্টিফিকেট অর্জন করুন।']],
            ],
            'stats' => [
                ['v' => '50+', 'l' => ['en' => 'Courses', 'bn' => 'কোর্স']],
                ['v' => '1000+', 'l' => ['en' => 'Students', 'bn' => 'শিক্ষার্থী']],
                ['v' => '12', 'l' => ['en' => 'Modules', 'bn' => 'মডিউল']],
                ['v' => '8', 'l' => ['en' => 'Payment options', 'bn' => 'পেমেন্ট অপশন']],
            ],
        ],
        'pricing' => [
            'hero' => [
                'titleA' => ['en' => 'Simple,', 'bn' => 'সহজ,'],
                'titleHl' => ['en' => 'honest pricing', 'bn' => 'স্বচ্ছ প্রাইসিং'],
                'subtitle' => ['en' => 'Pick a plan for your academy. Enable only the modules you need.', 'bn' => 'আপনার একাডেমির জন্য একটি প্ল্যান বেছে নিন। শুধু প্রয়োজনীয় মডিউল চালু করুন।'],
            ],
            'footnote' => ['en' => 'All plans include unlimited students and lifetime updates. Need something custom?', 'bn' => 'সব প্ল্যানে আনলিমিটেড শিক্ষার্থী ও লাইফটাইম আপডেট অন্তর্ভুক্ত। কাস্টম কিছু দরকার?'],
        ],
        'instructors' => [
            'hero' => [
                'titleA' => ['en' => 'Meet our', 'bn' => 'পরিচিত হোন আমাদের'],
                'titleHl' => ['en' => 'instructors', 'bn' => 'ইন্সট্রাক্টরদের সাথে'],
                'subtitle' => ['en' => 'Learn from experts who create and teach real courses.', 'bn' => 'বিশেষজ্ঞদের কাছ থেকে শিখুন যারা রিয়েল কোর্স তৈরি ও শেখান।'],
            ],
            'cta' => [
                'title' => ['en' => 'Want to teach on LMS?', 'bn' => 'LMS-এ শেখাতে চান?'],
                'text' => ['en' => 'Create and sell your own courses to students worldwide.', 'bn' => 'আপনার নিজের কোর্স তৈরি করুন ও বিশ্বজুড়ে শিক্ষার্থীদের কাছে বিক্রি করুন।'],
                'button' => ['en' => 'Become an instructor', 'bn' => 'ইন্সট্রাক্টর হন'],
            ],
        ],
        'contact' => [
            'hero' => [
                'titleA' => ['en' => 'Get in', 'bn' => 'যোগাযোগ'],
                'titleHl' => ['en' => 'touch', 'bn' => 'করুন'],
                'subtitle' => ['en' => 'Questions, feedback or partnership ideas? We\'d love to hear from you.', 'bn' => 'প্রশ্ন, মতামত বা পার্টনারশিপের আইডিয়া? আমরা আপনার কথা শুনতে চাই।'],
            ],
            'channels' => [
                ['icon' => '✉️', 'title' => ['en' => 'Email', 'bn' => 'ইমেইল'], 'value' => ['en' => 'support@lms.test', 'bn' => 'support@lms.test'], 'grad' => 'grad-primary'],
                ['icon' => '💬', 'title' => ['en' => 'Live chat', 'bn' => 'লাইভ চ্যাট'], 'value' => ['en' => 'Mon–Fri, 9am–6pm', 'bn' => 'সোম–শুক্র, সকাল ৯টা–সন্ধ্যা ৬টা'], 'grad' => 'grad-purple'],
                ['icon' => '📍', 'title' => ['en' => 'Office', 'bn' => 'অফিস'], 'value' => ['en' => 'Remote-first, worldwide', 'bn' => 'রিমোট-ফার্স্ট, বিশ্বজুড়ে'], 'grad' => 'grad-sunset'],
            ],
        ],
        'footer' => [
            'brand' => 'LMS',
            'tagline' => ['en' => 'Create, sell and teach courses. Grow your online academy — all in one bold platform.', 'bn' => 'কোর্স তৈরি, বিক্রি ও শেখান। আপনার অনলাইন একাডেমি গড়ে তুলুন — সব এক সাহসী প্ল্যাটফর্মে।'],
        ],
    ];

    /** Public: the effective content + any saved bilingual text overrides. */
    public function show()
    {
        return response()->json([
            'content' => self::effectiveContent(),
            'text' => Setting::get('text_overrides', (object) []),
        ]);
    }

    /** Defaults deep-merged with the saved override — usable from Blade/web too. */
    public static function effectiveContent(): array
    {
        $saved = Setting::get('site_content');

        return is_array($saved) ? self::deepMerge(self::DEFAULTS, $saved) : self::DEFAULTS;
    }

    /** Saved bilingual UI-text overrides (`{ key: {en,bn} }`). */
    public static function textOverrides(): array
    {
        $t = Setting::get('text_overrides', []);

        return is_array($t) ? $t : [];
    }

    /**
     * Admin: save bilingual text overrides for i18n keys — a map of
     * `{ "key": { "en": "...", "bn": "..." } }`. Only keys with at least one
     * non-empty value are stored; the rest fall back to the built-in dictionary.
     */
    public function saveText(Request $request)
    {
        // `present` (not `required`) so clearing every override — an empty map —
        // is still a valid save rather than a 422.
        $data = $request->validate([
            'text' => ['present', 'array'],
        ]);

        $clean = [];
        foreach ($data['text'] as $key => $val) {
            if (! is_array($val)) {
                continue;
            }
            $en = isset($val['en']) ? (string) $val['en'] : '';
            $bn = isset($val['bn']) ? (string) $val['bn'] : '';
            if ($en !== '' || $bn !== '') {
                $clean[$key] = ['en' => $en, 'bn' => $bn];
            }
        }

        Setting::set('text_overrides', $clean, 'content', 'json');

        return response()->json(['text' => $clean ?: (object) []]);
    }

    /** Admin: save overrides. Whatever is sent replaces that top-level section. */
    public function update(Request $request)
    {
        $data = $request->validate([
            'content' => ['required', 'array'],
        ]);

        // Only keep known top-level sections; store the merged result so the
        // saved value is always a complete, valid content tree.
        $incoming = array_intersect_key($data['content'], self::DEFAULTS);
        $merged = self::deepMerge(self::DEFAULTS, $incoming);

        Setting::set('site_content', $merged, 'content', 'json');

        return response()->json(['content' => $merged]);
    }

    /**
     * Deep-merge associative arrays. Lists (numeric-keyed arrays such as the
     * stats/stories rows) are replaced wholesale by the override, so deleting a
     * row actually deletes it rather than leaving the default behind.
     */
    private static function deepMerge(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (
                is_array($value) && isset($base[$key]) && is_array($base[$key])
                && self::isAssoc($value) && self::isAssoc($base[$key])
            ) {
                $base[$key] = self::deepMerge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    private static function isAssoc(array $arr): bool
    {
        if ($arr === []) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
