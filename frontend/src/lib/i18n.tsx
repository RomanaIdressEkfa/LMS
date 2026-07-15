"use client";

import { createContext, useContext, useEffect, useState, useCallback, ReactNode } from "react";
import { apiGet } from "./api";

export type Lang = "en" | "bn";

/**
 * Bilingual dictionary (English + Bangla). Add a key here in both languages
 * and use `t("key")` anywhere. Missing Bangla falls back to English.
 */
export const DICT: Record<string, { en: string; bn: string }> = {
  // ---- Nav ----
  "nav.courses": { en: "Courses", bn: "কোর্স" },
  "nav.instructors": { en: "Instructors", bn: "ইন্সট্রাক্টর" },
  "nav.pricing": { en: "Pricing", bn: "মূল্য" },
  "nav.about": { en: "About", bn: "আমাদের সম্পর্কে" },
  "nav.contact": { en: "Contact", bn: "যোগাযোগ" },
  "nav.login": { en: "Login", bn: "লগইন" },
  "nav.start": { en: "Start Learning", bn: "শেখা শুরু করুন" },
  "nav.dashboard": { en: "Go to Dashboard", bn: "ড্যাশবোর্ডে যান" },

  // ---- Home hero ----
  "home.badge": { en: "The complete learning platform", bn: "সম্পূর্ণ লার্নিং প্ল্যাটফর্ম" },
  "home.hero1": { en: "Learn & teach", bn: "শিখুন এবং শেখান" },
  "home.hero2": { en: "without limits.", bn: "কোনো সীমা ছাড়াই।" },
  "home.subtitle": {
    en: "Create and sell courses, run live classes, build quizzes, and grow your own online academy — all in one bold, colorful platform.",
    bn: "কোর্স তৈরি ও বিক্রি করুন, লাইভ ক্লাস নিন, কুইজ বানান এবং আপনার নিজের অনলাইন একাডেমি গড়ে তুলুন — সব এক প্ল্যাটফর্মে।",
  },
  "home.startFree": { en: "Start Learning Free →", bn: "ফ্রিতে শেখা শুরু করুন →" },
  "home.browse": { en: "Browse Courses", bn: "কোর্স দেখুন" },

  // ---- Stats ----
  "stat.courses": { en: "Courses", bn: "কোর্স" },
  "stat.modules": { en: "Modules", bn: "মডিউল" },
  "stat.gateways": { en: "Gateways", bn: "গেটওয়ে" },
  "stat.roles": { en: "Roles", bn: "রোল" },

  // ---- Features ----
  "feat.heading": { en: "Everything to run an academy", bn: "একাডেমি চালানোর সবকিছু" },
  "feat.sub": {
    en: "Turn features on or off — pay for and use only what you need.",
    bn: "প্রয়োজন অনুযায়ী ফিচার চালু বা বন্ধ করুন — শুধু যা লাগবে তার জন্যই পে করুন।",
  },
  "feat.sell": { en: "Sell Courses", bn: "কোর্স বিক্রি" },
  "feat.sell.d": { en: "Lessons, previews, free & paid access with instant enrollment.", bn: "লেসন, প্রিভিউ, ফ্রি ও পেইড অ্যাক্সেস এবং সাথে সাথে এনরোলমেন্ট।" },
  "feat.live": { en: "Live Classes", bn: "লাইভ ক্লাস" },
  "feat.live.d": { en: "Host live video sessions students join from their dashboard.", bn: "লাইভ ভিডিও ক্লাস নিন, শিক্ষার্থীরা ড্যাশবোর্ড থেকে যোগ দেবে।" },
  "feat.quiz": { en: "Quizzes", bn: "কুইজ" },
  "feat.quiz.d": { en: "Auto-graded quizzes with pass marks to test knowledge.", bn: "অটো-গ্রেডেড কুইজ ও পাস মার্ক দিয়ে জ্ঞান যাচাই।" },
  "feat.cert": { en: "Certificates", bn: "সার্টিফিকেট" },
  "feat.cert.d": { en: "Reward completion with certificates students love.", bn: "কোর্স শেষে শিক্ষার্থীদের সার্টিফিকেট দিন।" },
  "feat.pay": { en: "Payments", bn: "পেমেন্ট" },
  "feat.pay.d": { en: "Many gateways — enable whichever you want.", bn: "অনেক গেটওয়ে — যেটা চান সেটা চালু করুন।" },
  "feat.roles": { en: "Roles & Access", bn: "রোল ও অ্যাক্সেস" },
  "feat.roles.d": { en: "Build any role with exactly the permissions it needs.", bn: "যেকোনো রোল তৈরি করুন প্রয়োজন অনুযায়ী পারমিশন দিয়ে।" },

  // ---- Featured + CTA ----
  "home.featured": { en: "Featured courses", bn: "ফিচার্ড কোর্স" },
  "home.seeAll": { en: "See all courses →", bn: "সব কোর্স দেখুন →" },
  "cta.title": { en: "Ready to build your academy?", bn: "আপনার একাডেমি তৈরি করতে প্রস্তুত?" },
  "cta.sub": { en: "Join as a student, teacher, or organization and start today.", bn: "শিক্ষার্থী, শিক্ষক বা প্রতিষ্ঠান হিসেবে যোগ দিন — আজই শুরু করুন।" },
  "cta.createFree": { en: "Create free account", bn: "ফ্রি অ্যাকাউন্ট খুলুন" },
  "cta.login": { en: "Log in", bn: "লগইন" },
  "free": { en: "FREE", bn: "ফ্রি" },

  // ---- VIP homepage (Programming-Hero style) ----
  "ph.badge": { en: "AI-Driven Learning Platform", bn: "AI-চালিত লার্নিং প্ল্যাটফর্ম" },
  "ph.h1a": { en: "Become a", bn: "একজন" },
  "ph.h1b": { en: "Software Engineer", bn: "সফটওয়্যার ইঞ্জিনিয়ার" },
  "ph.h1c": { en: "& transform your career", bn: "হয়ে আপনার ক্যারিয়ার বদলে ফেলুন" },
  "ph.heroSub": { en: "Learn from real projects, live classes and expert mentors. Go from beginner to job-ready with a modern, industry-standard curriculum.", bn: "রিয়েল প্রজেক্ট, লাইভ ক্লাস আর এক্সপার্ট মেন্টরের কাছ থেকে শিখুন। ইন্ডাস্ট্রি-স্ট্যান্ডার্ড কারিকুলামে শূন্য থেকে জব-রেডি হয়ে উঠুন।" },
  "ph.cta1": { en: "Enroll Now", bn: "এনরোল করুন" },
  "ph.cta2": { en: "Explore Courses", bn: "কোর্স দেখুন" },
  "ph.badge1": { en: "100+ Videos", bn: "১০০+ ভিডিও" },
  "ph.badge2": { en: "Industry-Standard Curriculum", bn: "ইন্ডাস্ট্রি-স্ট্যান্ডার্ড কারিকুলাম" },
  "ph.badge3": { en: "6+ Real Projects", bn: "৬+ রিয়েল প্রজেক্ট" },
  "ph.techLabel": { en: "Technologies you'll master", bn: "যেসব টেকনোলজি শিখবেন" },

  "ph.whyTitle": { en: "Why learn with us?", bn: "কেন আমাদের সাথে শিখবেন?" },
  "ph.whySub": { en: "Everything you need to go from learner to professional.", bn: "শিক্ষার্থী থেকে প্রফেশনাল হওয়ার সবকিছু।" },
  "ph.why1": { en: "Industry-Standard Curriculum", bn: "ইন্ডাস্ট্রি-স্ট্যান্ডার্ড কারিকুলাম" },
  "ph.why1d": { en: "Content designed with what real companies actually need.", bn: "রিয়েল কোম্পানির চাহিদা অনুযায়ী তৈরি কনটেন্ট।" },
  "ph.why2": { en: "Live Classes & Support", bn: "লাইভ ক্লাস ও সাপোর্ট" },
  "ph.why2d": { en: "Learn live and get help whenever you're stuck.", bn: "লাইভ শিখুন, আটকে গেলে সাথে সাথে সাহায্য নিন।" },
  "ph.why3": { en: "Real Projects", bn: "রিয়েল প্রজেক্ট" },
  "ph.why3d": { en: "Build portfolio-ready projects, not just tutorials.", bn: "শুধু টিউটোরিয়াল নয় — পোর্টফোলিও-রেডি প্রজেক্ট বানান।" },
  "ph.why4": { en: "Job Placement Support", bn: "জব প্লেসমেন্ট সাপোর্ট" },
  "ph.why4d": { en: "CV, interviews and placement help until you're hired.", bn: "সিভি, ইন্টারভিউ ও প্লেসমেন্ট সাপোর্ট — জব পাওয়া পর্যন্ত।" },

  "ph.statsTitle": { en: "Our students' success record", bn: "আমাদের শিক্ষার্থীদের Success রেকর্ড" },
  "ph.stat1": { en: "Job Placement", bn: "জব প্লেসমেন্ট" },
  "ph.stat2": { en: "Countries", bn: "দেশ" },
  "ph.stat3": { en: "Hiring Companies", bn: "হায়ারিং কোম্পানি" },
  "ph.stat4": { en: "Happy Students", bn: "খুশি শিক্ষার্থী" },

  "ph.techTitle": { en: "The technologies in our superpower stack", bn: "আমাদের Superpower স্ট্যাকের টেকনোলজি" },
  "ph.techSub": { en: "Master the full modern stack, step by step.", bn: "ধাপে ধাপে সম্পূর্ণ মডার্ন স্ট্যাক শিখুন।" },

  "ph.storiesTitle": { en: "Success stories from our students", bn: "আমাদের শিক্ষার্থীদের সাফল্যের গল্প" },
  "ph.supportTitle": { en: "Constant support at every step", bn: "প্রতি ধাপে অবিরাম সাপোর্ট" },
  "ph.sup1": { en: "Q&A Sessions", bn: "প্রশ্ন-উত্তর সেশন" },
  "ph.sup1d": { en: "Ask anything and get answers fast.", bn: "যেকোনো প্রশ্ন করুন, দ্রুত উত্তর পান।" },
  "ph.sup2": { en: "Regular Support", bn: "নিয়মিত সাপোর্ট" },
  "ph.sup2d": { en: "Daily help from mentors and moderators.", bn: "মেন্টর ও মডারেটরদের প্রতিদিনের সাহায্য।" },
  "ph.sup3": { en: "Conceptual Sessions", bn: "কনসেপচুয়াল সেশন" },
  "ph.sup3d": { en: "Deep-dive sessions to clear the hard topics.", bn: "কঠিন টপিক ক্লিয়ার করতে ডিপ-ডাইভ সেশন।" },
  "ph.sup4": { en: "One-to-One", bn: "ওয়ান-টু-ওয়ান" },
  "ph.sup4d": { en: "Personal guidance when you need it.", bn: "প্রয়োজনে ব্যক্তিগত গাইডেন্স।" },
  "ph.sup5": { en: "Backlog Support", bn: "ব্যাকলগ সাপোর্ট" },
  "ph.sup5d": { en: "Fell behind? Catch up with dedicated help.", bn: "পিছিয়ে পড়েছেন? ডেডিকেটেড সাহায্যে এগিয়ে আসুন।" },
  "ph.sup6": { en: "Job Placement", bn: "জব প্লেসমেন্ট" },
  "ph.sup6d": { en: "Interview prep and referrals to hiring partners.", bn: "ইন্টারভিউ প্রস্তুতি ও হায়ারিং পার্টনারে রেফারেল।" },

  "ph.faqTitle": { en: "Frequently asked questions", bn: "সচরাচর জিজ্ঞাসিত প্রশ্ন" },
  "ph.faqQ1": { en: "Do I need prior experience?", bn: "আগে থেকে অভিজ্ঞতা লাগবে কি?" },
  "ph.faqA1": { en: "No. The curriculum starts from the basics and takes you to job-ready level.", bn: "না। কারিকুলাম একদম বেসিক থেকে শুরু করে জব-রেডি লেভেল পর্যন্ত নিয়ে যায়।" },
  "ph.faqQ2": { en: "Are the classes live or recorded?", bn: "ক্লাসগুলো কি লাইভ নাকি রেকর্ডেড?" },
  "ph.faqA2": { en: "Both — recorded lessons you can watch anytime, plus live sessions with mentors.", bn: "দুটোই — যেকোনো সময় দেখার রেকর্ডেড লেসন এবং মেন্টরের সাথে লাইভ সেশন।" },
  "ph.faqQ3": { en: "Will I get a certificate?", bn: "আমি কি সার্টিফিকেট পাব?" },
  "ph.faqA3": { en: "Yes, you earn a certificate on course completion.", bn: "হ্যাঁ, কোর্স শেষ করলে সার্টিফিকেট পাবেন।" },
  "ph.faqQ4": { en: "Is there job support?", bn: "জব সাপোর্ট আছে কি?" },
  "ph.faqA4": { en: "Yes — CV reviews, interview prep and referrals to hiring partners.", bn: "হ্যাঁ — সিভি রিভিউ, ইন্টারভিউ প্রস্তুতি ও হায়ারিং পার্টনারে রেফারেল।" },

  "ph.finalTitle": { en: "Ready to start your journey?", bn: "আপনার যাত্রা শুরু করতে প্রস্তুত?" },
  "ph.finalSub": { en: "Join thousands of learners building real careers with us.", bn: "হাজারো শিক্ষার্থীর সাথে যোগ দিন যারা রিয়েল ক্যারিয়ার গড়ছে।" },

  // ---- Dashboard sidebar ----
  "side.main": { en: "Main", bn: "মূল" },
  "side.education": { en: "Education", bn: "শিক্ষা" },
  "side.administration": { en: "Administration", bn: "অ্যাডমিনিস্ট্রেশন" },
  "side.platform": { en: "Platform", bn: "প্ল্যাটফর্ম" },
  "side.dashboard": { en: "Dashboard", bn: "ড্যাশবোর্ড" },
  "side.catalog": { en: "Course Catalog", bn: "কোর্স ক্যাটালগ" },
  "side.learning": { en: "My Learning", bn: "আমার শেখা" },
  "side.purchases": { en: "My Purchases", bn: "আমার কেনাকাটা" },
  "side.teaching": { en: "Teaching", bn: "শিক্ষকতা" },
  "side.live": { en: "Live Classes", bn: "লাইভ ক্লাস" },
  "side.quizzes": { en: "Quizzes", bn: "কুইজ" },
  "side.users": { en: "Users", bn: "ইউজার" },
  "side.roles": { en: "Roles & Permissions", bn: "রোল ও পারমিশন" },
  "side.modules": { en: "Modules / Addons", bn: "মডিউল / অ্যাডন" },
  "side.gateways": { en: "Payment Gateways", bn: "পেমেন্ট গেটওয়ে" },
  "side.content": { en: "Site Content", bn: "সাইট কনটেন্ট" },
  "side.settings": { en: "Settings", bn: "সেটিংস" },
  "side.tenants": { en: "Tenants", bn: "টেন্যান্ট" },
  "side.plans": { en: "Plans", bn: "প্ল্যান" },
  "topbar.logout": { en: "Logout", bn: "লগআউট" },

  // ---- Dashboard home ----
  "dash.hello": { en: "Hello", bn: "হ্যালো" },
  "dash.welcome": { en: "Welcome to your dashboard. Everything you can see here is controlled by your role's permissions.", bn: "আপনার ড্যাশবোর্ডে স্বাগতম। এখানে যা দেখছেন সবকিছু আপনার রোলের পারমিশন দিয়ে নিয়ন্ত্রিত।" },
  "dash.stat.courses": { en: "Enrolled Courses", bn: "এনরোল করা কোর্স" },
  "dash.stat.certs": { en: "Certificates", bn: "সার্টিফিকেট" },
  "dash.stat.live": { en: "Live Sessions", bn: "লাইভ সেশন" },
  "dash.stat.wallet": { en: "Wallet Balance", bn: "ওয়ালেট ব্যালেন্স" },
  "dash.access": { en: "Your access", bn: "আপনার অ্যাক্সেস" },
  "dash.accessSub": { en: "These are the permissions your role grants. The sidebar and every page adapt to exactly this list.", bn: "এগুলো আপনার রোলের দেওয়া পারমিশন। সাইডবার ও প্রতিটি পেজ ঠিক এই লিস্ট অনুযায়ী পরিবর্তিত হয়।" },

  // ---- Catalog (dashboard) ----
  "cat.title": { en: "Course Catalog", bn: "কোর্স ক্যাটালগ" },
  "cat.sub": { en: "Browse and enroll — free courses start instantly.", bn: "ব্রাউজ ও এনরোল করুন — ফ্রি কোর্স সাথে সাথে শুরু হয়।" },
  "cat.create": { en: "+ Create Course", bn: "+ কোর্স তৈরি করুন" },
  "cat.search": { en: "Search courses…", bn: "কোর্স খুঁজুন…" },
  "cat.allCats": { en: "All categories", bn: "সব ক্যাটাগরি" },
  "cat.all": { en: "All", bn: "সব" },
  "cat.freeF": { en: "Free", bn: "ফ্রি" },
  "cat.paid": { en: "Paid", bn: "পেইড" },
  "cat.loading": { en: "Loading courses…", bn: "কোর্স লোড হচ্ছে…" },
  "cat.none": { en: "No courses match your filters.", bn: "আপনার ফিল্টারে কোনো কোর্স মেলেনি।" },
  "cat.lessons": { en: "lessons", bn: "লেসন" },

  // ---- My Learning ----
  "learn.title": { en: "My Learning", bn: "আমার শেখা" },
  "learn.sub": { en: "Pick up where you left off.", bn: "যেখানে থেমেছিলেন সেখান থেকে শুরু করুন।" },
  "learn.empty": { en: "You haven't enrolled in any courses yet.", bn: "আপনি এখনো কোনো কোর্সে এনরোল করেননি।" },
  "learn.browse": { en: "Browse Catalog", bn: "ক্যাটালগ দেখুন" },
  "learn.completed": { en: "Completed", bn: "সম্পন্ন" },
  "learn.continue": { en: "Continue", bn: "চালিয়ে যান" },
  "common.loading": { en: "Loading…", bn: "লোড হচ্ছে…" },

  // ---- Quizzes ----
  "quiz.title": { en: "Quizzes", bn: "কুইজ" },
  "quiz.sub": { en: "Test your knowledge and earn passing scores.", bn: "আপনার জ্ঞান যাচাই করুন এবং পাস স্কোর অর্জন করুন।" },
  "quiz.new": { en: "+ New Quiz", bn: "+ নতুন কুইজ" },
  "quiz.mine": { en: "My quizzes", bn: "আমার কুইজ" },
  "quiz.mineEmpty": { en: "You haven't created any quizzes yet.", bn: "আপনি এখনো কোনো কুইজ তৈরি করেননি।" },
  "quiz.available": { en: "Available quizzes", bn: "উপলব্ধ কুইজ" },
  "quiz.availEmpty": { en: "No published quizzes yet.", bn: "এখনো কোনো প্রকাশিত কুইজ নেই।" },
  "quiz.questions": { en: "questions", bn: "প্রশ্ন" },
  "quiz.pass": { en: "pass", bn: "পাস" },
  "quiz.attempts": { en: "attempts", bn: "অ্যাটেম্পট" },
  "quiz.published": { en: "published", bn: "প্রকাশিত" },
  "quiz.draft": { en: "draft", bn: "খসড়া" },
  "quiz.best": { en: "Best", bn: "সেরা" },
  "quiz.start": { en: "Start quiz", bn: "কুইজ শুরু করুন" },
  "quiz.retake": { en: "Retake", bn: "পুনরায় দিন" },
  "quiz.passMark": { en: "Pass mark", bn: "পাস মার্ক" },
  "quiz.answered": { en: "answered", bn: "উত্তর দেওয়া" },
  "quiz.submit": { en: "Submit quiz", bn: "কুইজ জমা দিন" },
  "quiz.grading": { en: "Grading…", bn: "গ্রেডিং হচ্ছে…" },
  "quiz.passed": { en: "Passed!", bn: "পাস করেছেন!" },
  "quiz.notPassed": { en: "Not passed", bn: "পাস করেননি" },
  "quiz.need": { en: "need", bn: "প্রয়োজন" },
  "quiz.gotPoints": { en: "You got {e} of {t} points.", bn: "আপনি {t} এর মধ্যে {e} পয়েন্ট পেয়েছেন।" },
  "quiz.allQuizzes": { en: "All quizzes", bn: "সব কুইজ" },
  "quiz.notFound": { en: "Quiz not found or unavailable.", bn: "কুইজ পাওয়া যায়নি বা উপলব্ধ নয়।" },

  // ---- Live classes ----
  "live.title": { en: "Live Classes", bn: "লাইভ ক্লাস" },
  "live.sub": { en: "Join live sessions or watch for what's coming up.", bn: "লাইভ সেশনে যোগ দিন বা আসন্ন ক্লাসগুলো দেখুন।" },
  "live.schedule": { en: "+ Schedule Session", bn: "+ সেশন শিডিউল করুন" },
  "live.close": { en: "Close", bn: "বন্ধ করুন" },
  "live.empty": { en: "No upcoming live sessions.", bn: "কোনো আসন্ন লাইভ সেশন নেই।" },
  "live.join": { en: "Join now", bn: "এখন যোগ দিন" },
  "live.opensWhenLive": { en: "Link opens when the session goes live", bn: "সেশন লাইভ হলে লিঙ্ক দেখা যাবে" },
  "live.goLive": { en: "Go live", bn: "লাইভ করুন" },
  "live.end": { en: "End", bn: "শেষ করুন" },
  "live.badgeLive": { en: "Live", bn: "লাইভ" },
  "live.scheduled": { en: "scheduled", bn: "শিডিউলড" },

  // ---- My Purchases ----
  "buy.title": { en: "My Purchases", bn: "আমার কেনাকাটা" },
  "buy.sub": { en: "Your order history and receipts.", bn: "আপনার অর্ডার হিস্ট্রি ও রসিদ।" },
  "buy.empty": { en: "No purchases yet.", bn: "এখনো কোনো কেনাকাটা নেই।" },
  "buy.order": { en: "Order", bn: "অর্ডার" },
  "buy.course": { en: "Course", bn: "কোর্স" },
  "buy.method": { en: "Method", bn: "মাধ্যম" },
  "buy.amount": { en: "Amount", bn: "পরিমাণ" },
  "buy.status": { en: "Status", bn: "স্ট্যাটাস" },
};

interface LangCtx {
  lang: Lang;
  setLang: (l: Lang) => void;
  t: (key: string) => string;
}

const Ctx = createContext<LangCtx | null>(null);
const STORAGE = "nova_lang";

type Overrides = Record<string, { en?: string; bn?: string }>;

export function LanguageProvider({ children }: { children: ReactNode }) {
  const [lang, setLangState] = useState<Lang>("en");
  // Admin-configured bilingual overrides for any dictionary key.
  const [overrides, setOverrides] = useState<Overrides>({});

  useEffect(() => {
    const saved = (typeof window !== "undefined" && localStorage.getItem(STORAGE)) as Lang | null;
    const hasChoice = saved === "en" || saved === "bn";
    if (hasChoice) setLangState(saved as Lang);

    // Always load bootstrap for text overrides (and the default language when
    // the visitor hasn't chosen one yet).
    apiGet<{ settings: { default_language?: string }; text_overrides?: Overrides }>("/bootstrap")
      .then((d) => {
        if (!hasChoice) {
          const def = d.settings?.default_language;
          if (def === "bn" || def === "en") setLangState(def);
        }
        if (d.text_overrides && !Array.isArray(d.text_overrides)) {
          setOverrides(d.text_overrides);
        }
      })
      .catch(() => {});
  }, []);

  const setLang = useCallback((l: Lang) => {
    setLangState(l);
    if (typeof window !== "undefined") localStorage.setItem(STORAGE, l);
    document.documentElement.lang = l;
  }, []);

  const t = useCallback(
    (key: string) =>
      overrides[key]?.[lang] || DICT[key]?.[lang] || DICT[key]?.en || key,
    [lang, overrides]
  );

  return <Ctx.Provider value={{ lang, setLang, t }}>{children}</Ctx.Provider>;
}

export function useLang() {
  const ctx = useContext(Ctx);
  if (!ctx) throw new Error("useLang must be used within <LanguageProvider>");
  return ctx;
}
