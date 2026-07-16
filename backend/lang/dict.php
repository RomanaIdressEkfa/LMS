<?php

/**
 * Bilingual UI dictionary (English + Bangla), ported 1:1 from the old Next.js
 * i18n DICT. Resolved via App\Support\Translations and rendered with the
 * <x-t> Blade component, which reacts to the Alpine `lang` store for instant
 * EN/BN switching. Admin text_overrides (site_content) win over these.
 */
return [
    // ---- Nav ----
    'nav.courses' => ['en' => 'Courses', 'bn' => 'কোর্স'],
    'nav.instructors' => ['en' => 'Instructors', 'bn' => 'ইন্সট্রাক্টর'],
    'nav.pricing' => ['en' => 'Pricing', 'bn' => 'মূল্য'],
    'nav.about' => ['en' => 'About', 'bn' => 'আমাদের সম্পর্কে'],
    'nav.contact' => ['en' => 'Contact', 'bn' => 'যোগাযোগ'],
    'nav.login' => ['en' => 'Login', 'bn' => 'লগইন'],
    'nav.start' => ['en' => 'Start Learning', 'bn' => 'শেখা শুরু করুন'],
    'nav.dashboard' => ['en' => 'Go to Dashboard', 'bn' => 'ড্যাশবোর্ডে যান'],

    // ---- Featured + CTA ----
    'home.featured' => ['en' => 'Featured courses', 'bn' => 'ফিচার্ড কোর্স'],
    'home.seeAll' => ['en' => 'See all courses →', 'bn' => 'সব কোর্স দেখুন →'],
    'cta.createFree' => ['en' => 'Create free account', 'bn' => 'ফ্রি অ্যাকাউন্ট খুলুন'],
    'cta.login' => ['en' => 'Log in', 'bn' => 'লগইন'],
    'free' => ['en' => 'FREE', 'bn' => 'ফ্রি'],

    // ---- VIP homepage (Programming-Hero style) ----
    'ph.badge' => ['en' => 'AI-Driven Learning Platform', 'bn' => 'AI-চালিত লার্নিং প্ল্যাটফর্ম'],
    'ph.h1a' => ['en' => 'Become a', 'bn' => 'একজন'],
    'ph.h1b' => ['en' => 'Software Engineer', 'bn' => 'সফটওয়্যার ইঞ্জিনিয়ার'],
    'ph.h1c' => ['en' => '& transform your career', 'bn' => 'হয়ে আপনার ক্যারিয়ার বদলে ফেলুন'],
    'ph.heroSub' => ['en' => 'Learn from real projects, live classes and expert mentors. Go from beginner to job-ready with a modern, industry-standard curriculum.', 'bn' => 'রিয়েল প্রজেক্ট, লাইভ ক্লাস আর এক্সপার্ট মেন্টরের কাছ থেকে শিখুন। ইন্ডাস্ট্রি-স্ট্যান্ডার্ড কারিকুলামে শূন্য থেকে জব-রেডি হয়ে উঠুন।'],
    'ph.cta1' => ['en' => 'Enroll Now', 'bn' => 'এনরোল করুন'],
    'ph.cta2' => ['en' => 'Explore Courses', 'bn' => 'কোর্স দেখুন'],
    'ph.badge1' => ['en' => '100+ Videos', 'bn' => '১০০+ ভিডিও'],
    'ph.badge2' => ['en' => 'Industry-Standard Curriculum', 'bn' => 'ইন্ডাস্ট্রি-স্ট্যান্ডার্ড কারিকুলাম'],
    'ph.badge3' => ['en' => '6+ Real Projects', 'bn' => '৬+ রিয়েল প্রজেক্ট'],
    'ph.techLabel' => ['en' => "Technologies you'll master", 'bn' => 'যেসব টেকনোলজি শিখবেন'],

    'ph.whyTitle' => ['en' => 'Why learn with us?', 'bn' => 'কেন আমাদের সাথে শিখবেন?'],
    'ph.whySub' => ['en' => 'Everything you need to go from learner to professional.', 'bn' => 'শিক্ষার্থী থেকে প্রফেশনাল হওয়ার সবকিছু।'],
    'ph.why1' => ['en' => 'Industry-Standard Curriculum', 'bn' => 'ইন্ডাস্ট্রি-স্ট্যান্ডার্ড কারিকুলাম'],
    'ph.why1d' => ['en' => 'Content designed with what real companies actually need.', 'bn' => 'রিয়েল কোম্পানির চাহিদা অনুযায়ী তৈরি কনটেন্ট।'],
    'ph.why2' => ['en' => 'Live Classes & Support', 'bn' => 'লাইভ ক্লাস ও সাপোর্ট'],
    'ph.why2d' => ['en' => "Learn live and get help whenever you're stuck.", 'bn' => 'লাইভ শিখুন, আটকে গেলে সাথে সাথে সাহায্য নিন।'],
    'ph.why3' => ['en' => 'Real Projects', 'bn' => 'রিয়েল প্রজেক্ট'],
    'ph.why3d' => ['en' => 'Build portfolio-ready projects, not just tutorials.', 'bn' => 'শুধু টিউটোরিয়াল নয় — পোর্টফোলিও-রেডি প্রজেক্ট বানান।'],
    'ph.why4' => ['en' => 'Job Placement Support', 'bn' => 'জব প্লেসমেন্ট সাপোর্ট'],
    'ph.why4d' => ['en' => "CV, interviews and placement help until you're hired.", 'bn' => 'সিভি, ইন্টারভিউ ও প্লেসমেন্ট সাপোর্ট — জব পাওয়া পর্যন্ত।'],

    'ph.statsTitle' => ['en' => "Our students' success record", 'bn' => 'আমাদের শিক্ষার্থীদের Success রেকর্ড'],
    'ph.techTitle' => ['en' => 'The technologies in our superpower stack', 'bn' => 'আমাদের Superpower স্ট্যাকের টেকনোলজি'],
    'ph.techSub' => ['en' => 'Master the full modern stack, step by step.', 'bn' => 'ধাপে ধাপে সম্পূর্ণ মডার্ন স্ট্যাক শিখুন।'],

    'ph.storiesTitle' => ['en' => 'Success stories from our students', 'bn' => 'আমাদের শিক্ষার্থীদের সাফল্যের গল্প'],
    'ph.supportTitle' => ['en' => 'Constant support at every step', 'bn' => 'প্রতি ধাপে অবিরাম সাপোর্ট'],
    'ph.sup1' => ['en' => 'Q&A Sessions', 'bn' => 'প্রশ্ন-উত্তর সেশন'],
    'ph.sup1d' => ['en' => 'Ask anything and get answers fast.', 'bn' => 'যেকোনো প্রশ্ন করুন, দ্রুত উত্তর পান।'],
    'ph.sup2' => ['en' => 'Regular Support', 'bn' => 'নিয়মিত সাপোর্ট'],
    'ph.sup2d' => ['en' => 'Daily help from mentors and moderators.', 'bn' => 'মেন্টর ও মডারেটরদের প্রতিদিনের সাহায্য।'],
    'ph.sup3' => ['en' => 'Conceptual Sessions', 'bn' => 'কনসেপচুয়াল সেশন'],
    'ph.sup3d' => ['en' => 'Deep-dive sessions to clear the hard topics.', 'bn' => 'কঠিন টপিক ক্লিয়ার করতে ডিপ-ডাইভ সেশন।'],
    'ph.sup4' => ['en' => 'One-to-One', 'bn' => 'ওয়ান-টু-ওয়ান'],
    'ph.sup4d' => ['en' => 'Personal guidance when you need it.', 'bn' => 'প্রয়োজনে ব্যক্তিগত গাইডেন্স।'],
    'ph.sup5' => ['en' => 'Backlog Support', 'bn' => 'ব্যাকলগ সাপোর্ট'],
    'ph.sup5d' => ['en' => 'Fell behind? Catch up with dedicated help.', 'bn' => 'পিছিয়ে পড়েছেন? ডেডিকেটেড সাহায্যে এগিয়ে আসুন।'],
    'ph.sup6' => ['en' => 'Job Placement', 'bn' => 'জব প্লেসমেন্ট'],
    'ph.sup6d' => ['en' => 'Interview prep and referrals to hiring partners.', 'bn' => 'ইন্টারভিউ প্রস্তুতি ও হায়ারিং পার্টনারে রেফারেল।'],

    'ph.faqTitle' => ['en' => 'Frequently asked questions', 'bn' => 'সচরাচর জিজ্ঞাসিত প্রশ্ন'],
    'ph.faqQ1' => ['en' => 'Do I need prior experience?', 'bn' => 'আগে থেকে অভিজ্ঞতা লাগবে কি?'],
    'ph.faqA1' => ['en' => 'No. The curriculum starts from the basics and takes you to job-ready level.', 'bn' => 'না। কারিকুলাম একদম বেসিক থেকে শুরু করে জব-রেডি লেভেল পর্যন্ত নিয়ে যায়।'],
    'ph.faqQ2' => ['en' => 'Are the classes live or recorded?', 'bn' => 'ক্লাসগুলো কি লাইভ নাকি রেকর্ডেড?'],
    'ph.faqA2' => ['en' => 'Both — recorded lessons you can watch anytime, plus live sessions with mentors.', 'bn' => 'দুটোই — যেকোনো সময় দেখার রেকর্ডেড লেসন এবং মেন্টরের সাথে লাইভ সেশন।'],
    'ph.faqQ3' => ['en' => 'Will I get a certificate?', 'bn' => 'আমি কি সার্টিফিকেট পাব?'],
    'ph.faqA3' => ['en' => 'Yes, you earn a certificate on course completion.', 'bn' => 'হ্যাঁ, কোর্স শেষ করলে সার্টিফিকেট পাবেন।'],
    'ph.faqQ4' => ['en' => 'Is there job support?', 'bn' => 'জব সাপোর্ট আছে কি?'],
    'ph.faqA4' => ['en' => 'Yes — CV reviews, interview prep and referrals to hiring partners.', 'bn' => 'হ্যাঁ — সিভি রিভিউ, ইন্টারভিউ প্রস্তুতি ও হায়ারিং পার্টনারে রেফারেল।'],

    'ph.finalTitle' => ['en' => 'Ready to start your journey?', 'bn' => 'আপনার যাত্রা শুরু করতে প্রস্তুত?'],
    'ph.finalSub' => ['en' => 'Join thousands of learners building real careers with us.', 'bn' => 'হাজারো শিক্ষার্থীর সাথে যোগ দিন যারা রিয়েল ক্যারিয়ার গড়ছে।'],

    // ---- Catalog / My Learning (used by later phases) ----
    'cat.lessons' => ['en' => 'lessons', 'bn' => 'লেসন'],
    'learn.title' => ['en' => 'My Learning', 'bn' => 'আমার শেখা'],
    'learn.completed' => ['en' => 'Completed', 'bn' => 'সম্পন্ন'],
    'learn.continue' => ['en' => 'Continue', 'bn' => 'চালিয়ে যান'],
    'common.loading' => ['en' => 'Loading…', 'bn' => 'লোড হচ্ছে…'],
];
