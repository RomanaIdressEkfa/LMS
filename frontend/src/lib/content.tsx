"use client";

import { createContext, useContext, useEffect, useState, ReactNode } from "react";
import { apiGet } from "./api";
import type { Lang } from "./i18n";

/* ---------------------------------------------------------------
   Editable, bilingual marketing content for the public site.
   Prose fields are `Loc` ({en,bn}); styling tokens / identifiers
   (icon, grad, bg, numbers, names) stay plain strings. Mirrors the
   backend SiteContentController::DEFAULTS so the first paint always
   has content; the /content fetch then swaps in admin overrides.
----------------------------------------------------------------*/

export type Loc = { en: string; bn: string };

/** Resolve a possibly-bilingual value to a string for the active language. */
export function loc(v: Loc | string | undefined | null, lang: Lang): string {
  if (v == null) return "";
  if (typeof v === "string") return v;
  return v[lang] || v.en || "";
}

const L = (en: string, bn: string): Loc => ({ en, bn });

// Index signature so these rows fit the admin editor's Record<string, string|Loc> helpers.
export interface StatItem { v: string; l: Loc; [k: string]: string | Loc }
export interface StackItem { icon: string; name: string; d: Loc; bg: string; [k: string]: string | Loc }
export interface StoryItem { name: string; role: Loc; grad: string; text: Loc; [k: string]: string | Loc }
export interface ValueItem { icon: string; title: Loc; text: Loc; grad: string; [k: string]: string | Loc }
export interface StepItem { n: string; title: Loc; text: Loc; [k: string]: string | Loc }
export interface ChannelItem { icon: string; title: Loc; value: Loc; grad: string; [k: string]: string | Loc }
export interface Hero { titleA: Loc; titleHl: Loc; subtitle: Loc }

export interface SiteContent {
  home: {
    techStrip: string[];
    stats: StatItem[];
    stack: StackItem[];
    stories: StoryItem[];
  };
  about: {
    hero: Hero;
    values: ValueItem[];
    steps: StepItem[];
    stats: StatItem[];
  };
  pricing: {
    hero: Hero;
    footnote: Loc;
  };
  instructors: {
    hero: Hero;
    cta: { title: Loc; text: Loc; button: Loc };
  };
  contact: {
    hero: Hero;
    channels: ChannelItem[];
  };
}

export const CONTENT_DEFAULTS: SiteContent = {
  home: {
    techStrip: ["JavaScript", "TypeScript", "React", "Next.js", "Node.js", "Express", "MongoDB", "PostgreSQL", "Prisma", "Tailwind", "Docker", "AI"],
    stats: [
      { v: "75%", l: L("Job Placement", "জব প্লেসমেন্ট") },
      { v: "25+", l: L("Countries", "দেশ") },
      { v: "230+", l: L("Hiring Companies", "হায়ারিং কোম্পানি") },
      { v: "10K+", l: L("Happy Students", "খুশি শিক্ষার্থী") },
    ],
    stack: [
      { icon: "🟨", name: "JavaScript", d: L("The language of the web — from fundamentals to advanced patterns.", "ওয়েবের ভাষা — বেসিক থেকে অ্যাডভান্সড প্যাটার্ন পর্যন্ত।"), bg: "pastel-amber" },
      { icon: "🔷", name: "TypeScript", d: L("Type-safe JavaScript for scalable, bug-free applications.", "স্কেলেবল, বাগ-মুক্ত অ্যাপের জন্য টাইপ-সেফ জাভাস্ক্রিপ্ট।"), bg: "pastel-blue" },
      { icon: "⚛️", name: "React", d: L("Build modern, interactive user interfaces with components.", "কম্পোনেন্ট দিয়ে আধুনিক, ইন্টারঅ্যাকটিভ ইউজার ইন্টারফেস তৈরি করুন।"), bg: "pastel-cyan" },
      { icon: "▲", name: "Next.js", d: L("Production React framework with routing, SSR and APIs.", "রাউটিং, SSR ও API সহ প্রোডাকশন React ফ্রেমওয়ার্ক।"), bg: "pastel-purple" },
      { icon: "🟩", name: "Node & Express", d: L("Build fast, scalable backend APIs and services.", "দ্রুত, স্কেলেবল ব্যাকএন্ড API ও সার্ভিস তৈরি করুন।"), bg: "pastel-green" },
      { icon: "🗄️", name: "Databases", d: L("MongoDB, PostgreSQL & Prisma — model and query real data.", "MongoDB, PostgreSQL ও Prisma — রিয়েল ডেটা মডেল ও কোয়েরি করুন।"), bg: "pastel-pink" },
      { icon: "🎨", name: "Tailwind CSS", d: L("Design beautiful, responsive UIs quickly.", "দ্রুত সুন্দর, রেসপন্সিভ UI ডিজাইন করুন।"), bg: "pastel-cyan" },
      { icon: "🤖", name: "AI Engineering", d: L("Build AI-powered features into real applications.", "রিয়েল অ্যাপে AI-চালিত ফিচার যুক্ত করুন।"), bg: "pastel-purple" },
    ],
    stories: [
      { name: "Rahim Ahmed", role: L("Frontend Engineer @ Startup", "ফ্রন্টএন্ড ইঞ্জিনিয়ার @ স্টার্টআপ"), grad: "grad-primary", text: L("The projects made my portfolio stand out. I got hired within 2 months of finishing.", "প্রজেক্টগুলো আমার পোর্টফোলিও আলাদা করে তুলেছে। শেষ করার ২ মাসের মধ্যেই চাকরি পেয়েছি।") },
      { name: "Nusrat Jahan", role: L("Full-Stack Developer", "ফুল-স্ট্যাক ডেভেলপার"), grad: "grad-purple", text: L("Live classes and mentor support kept me going. Best decision for my career.", "লাইভ ক্লাস আর মেন্টর সাপোর্ট আমাকে চালিয়ে যেতে সাহায্য করেছে। ক্যারিয়ারের সেরা সিদ্ধান্ত।") },
      { name: "Tanvir Hasan", role: L("Software Engineer", "সফটওয়্যার ইঞ্জিনিয়ার"), grad: "grad-sunset", text: L("From zero coding to a real job. The curriculum is genuinely industry-standard.", "শূন্য কোডিং থেকে রিয়েল চাকরি। কারিকুলাম সত্যিই ইন্ডাস্ট্রি-স্ট্যান্ডার্ড।") },
    ],
  },
  about: {
    hero: {
      titleA: L("Learning,", "শেখা,"),
      titleHl: L("reimagined", "নতুনভাবে"),
      subtitle: L(
        "LMS is a modern platform for creating, selling and teaching courses — with live classes, quizzes, certificates and flexible pricing. Whether you're a solo instructor or a growing organization, everything you need is here.",
        "LMS হলো কোর্স তৈরি, বিক্রি ও শেখানোর একটি আধুনিক প্ল্যাটফর্ম — লাইভ ক্লাস, কুইজ, সার্টিফিকেট ও নমনীয় প্রাইসিং সহ। আপনি একক ইন্সট্রাক্টর হোন বা বাড়তে থাকা প্রতিষ্ঠান, প্রয়োজনীয় সবকিছু এখানেই।"
      ),
    },
    values: [
      { icon: "🎯", title: L("Learner-first", "শিক্ষার্থী-প্রথম"), text: L("Every feature is built to help students actually learn and finish.", "প্রতিটি ফিচার শিক্ষার্থীদের সত্যিকারভাবে শিখতে ও শেষ করতে সাহায্য করার জন্য তৈরি।"), grad: "grad-primary" },
      { icon: "🧩", title: L("Only what you need", "শুধু যা প্রয়োজন"), text: L("Turn modules on or off — never pay for features you won't use.", "মডিউল চালু বা বন্ধ করুন — যে ফিচার ব্যবহার করবেন না তার জন্য কখনো পে করবেন না।"), grad: "grad-purple" },
      { icon: "⚡", title: L("Fast & modern", "দ্রুত ও আধুনিক"), text: L("Built on Laravel + Next.js for a snappy, reliable experience.", "Laravel + Next.js-এ তৈরি, স্ন্যাপি ও নির্ভরযোগ্য অভিজ্ঞতার জন্য।"), grad: "grad-sunset" },
      { icon: "🌍", title: L("For everyone", "সবার জন্য"), text: L("Students, instructors and organizations — all in one platform.", "শিক্ষার্থী, ইন্সট্রাক্টর ও প্রতিষ্ঠান — সব এক প্ল্যাটফর্মে।"), grad: "grad-teal" },
    ],
    steps: [
      { n: "1", title: L("Create your account", "আপনার অ্যাকাউন্ট তৈরি করুন"), text: L("Sign up as a student, instructor or organization in seconds.", "শিক্ষার্থী, ইন্সট্রাক্টর বা প্রতিষ্ঠান হিসেবে কয়েক সেকেন্ডে সাইন আপ করুন।") },
      { n: "2", title: L("Build or enroll", "তৈরি করুন বা এনরোল করুন"), text: L("Instructors build courses; students enroll — free or paid.", "ইন্সট্রাক্টররা কোর্স তৈরি করেন; শিক্ষার্থীরা এনরোল করে — ফ্রি বা পেইড।") },
      { n: "3", title: L("Learn & grow", "শিখুন ও এগিয়ে যান"), text: L("Take lessons, join live classes, pass quizzes, earn certificates.", "লেসন নিন, লাইভ ক্লাসে যোগ দিন, কুইজ পাস করুন, সার্টিফিকেট অর্জন করুন।") },
    ],
    stats: [
      { v: "50+", l: L("Courses", "কোর্স") },
      { v: "1000+", l: L("Students", "শিক্ষার্থী") },
      { v: "12", l: L("Modules", "মডিউল") },
      { v: "8", l: L("Payment options", "পেমেন্ট অপশন") },
    ],
  },
  pricing: {
    hero: {
      titleA: L("Simple,", "সহজ,"),
      titleHl: L("honest pricing", "স্বচ্ছ প্রাইসিং"),
      subtitle: L("Pick a plan for your academy. Enable only the modules you need.", "আপনার একাডেমির জন্য একটি প্ল্যান বেছে নিন। শুধু প্রয়োজনীয় মডিউল চালু করুন।"),
    },
    footnote: L("All plans include unlimited students and lifetime updates. Need something custom?", "সব প্ল্যানে আনলিমিটেড শিক্ষার্থী ও লাইফটাইম আপডেট অন্তর্ভুক্ত। কাস্টম কিছু দরকার?"),
  },
  instructors: {
    hero: {
      titleA: L("Meet our", "পরিচিত হোন আমাদের"),
      titleHl: L("instructors", "ইন্সট্রাক্টরদের সাথে"),
      subtitle: L("Learn from experts who create and teach real courses.", "বিশেষজ্ঞদের কাছ থেকে শিখুন যারা রিয়েল কোর্স তৈরি ও শেখান।"),
    },
    cta: {
      title: L("Want to teach on LMS?", "LMS-এ শেখাতে চান?"),
      text: L("Create and sell your own courses to students worldwide.", "আপনার নিজের কোর্স তৈরি করুন ও বিশ্বজুড়ে শিক্ষার্থীদের কাছে বিক্রি করুন।"),
      button: L("Become an instructor", "ইন্সট্রাক্টর হন"),
    },
  },
  contact: {
    hero: {
      titleA: L("Get in", "যোগাযোগ"),
      titleHl: L("touch", "করুন"),
      subtitle: L("Questions, feedback or partnership ideas? We'd love to hear from you.", "প্রশ্ন, মতামত বা পার্টনারশিপের আইডিয়া? আমরা আপনার কথা শুনতে চাই।"),
    },
    channels: [
      { icon: "✉️", title: L("Email", "ইমেইল"), value: L("support@lms.test", "support@lms.test"), grad: "grad-primary" },
      { icon: "💬", title: L("Live chat", "লাইভ চ্যাট"), value: L("Mon–Fri, 9am–6pm", "সোম–শুক্র, সকাল ৯টা–সন্ধ্যা ৬টা"), grad: "grad-purple" },
      { icon: "📍", title: L("Office", "অফিস"), value: L("Remote-first, worldwide", "রিমোট-ফার্স্ট, বিশ্বজুড়ে"), grad: "grad-sunset" },
    ],
  },
};

const Ctx = createContext<{ content: SiteContent }>({ content: CONTENT_DEFAULTS });

export function ContentProvider({ children }: { children: ReactNode }) {
  const [content, setContent] = useState<SiteContent>(CONTENT_DEFAULTS);

  useEffect(() => {
    apiGet<{ content: SiteContent }>("/content")
      .then((d) => { if (d.content) setContent(d.content); })
      .catch(() => {});
  }, []);

  return <Ctx.Provider value={{ content }}>{children}</Ctx.Provider>;
}

export function useContent() {
  return useContext(Ctx).content;
}
