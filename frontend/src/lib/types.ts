export interface User {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  avatar: string | null;
  bio: string | null;
  status: string;
  roles: string[];
  permissions: string[];
}

export interface AuthResponse {
  user: User;
  token: string;
}

export interface Module {
  id: number;
  key: string;
  name: string;
  description: string | null;
  icon: string | null;
  category: string;
  enabled: boolean;
  is_core: boolean;
  sort_order: number;
}

export interface Role {
  id: number;
  name: string;
  users_count: number;
  is_protected: boolean;
  permissions: string[];
}

export interface PaymentGateway {
  id: number;
  key: string;
  name: string;
  logo: string | null;
  enabled: boolean;
  test_mode: boolean;
  currency: string;
}

export type RegisterRole = "student" | "teacher" | "organization";

export interface Category {
  id: number;
  name: string;
  slug: string;
  icon: string | null;
  color: string;
  courses_count?: number;
}

export interface CourseTeacher {
  id: number;
  name: string;
  avatar: string | null;
  bio?: string | null;
}

export interface Lesson {
  id: number;
  title: string;
  type: "video" | "text" | "pdf";
  duration_minutes: number;
  is_preview: boolean;
  locked?: boolean;
  video_url?: string | null;
  content?: string | null;
  sort_order?: number;
}

export interface Course {
  id: number;
  teacher_id: number;
  category_id: number | null;
  title: string;
  slug: string;
  subtitle: string | null;
  description: string | null;
  thumbnail: string | null;
  level: "beginner" | "intermediate" | "advanced";
  is_free: boolean;
  price: string | number;
  status: "draft" | "pending" | "published";
  duration_minutes: number;
  lessons_count?: number;
  enrollments_count?: number;
  teacher?: CourseTeacher;
  category?: Category | null;
  lessons?: Lesson[];
  is_enrolled?: boolean;
  is_owner?: boolean;
}

export interface Enrollment {
  id: number;
  course_id: number;
  progress: number;
  source: string;
  completed_at: string | null;
  course: Course;
}

export interface Paginated<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}
