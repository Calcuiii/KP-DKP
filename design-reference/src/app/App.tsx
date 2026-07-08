import { useState, useRef, useEffect } from "react";
import {
  Fish, MessageSquare, FileText, BarChart2, Settings, Users, LogOut,
  ChevronDown, ChevronRight, Send, Copy, RefreshCw, ThumbsUp, ThumbsDown,
  Search, Bell, Plus, Upload, Eye, Edit2, Trash2, RotateCcw, Filter,
  CheckCircle, Clock, XCircle, AlertCircle, Menu, X, ArrowRight,
  BookOpen, Shield, Zap, TrendingUp, Activity, Database, MessageCircle,
  HelpCircle, Home, ChevronLeft, Star, MoreVertical, Download,
  FileCheck, Layers, Hash, Calendar, User, Lock, EyeOff, ChevronUp,
  ExternalLink, Info, Award, Target, Inbox
} from "lucide-react";
import {
  LineChart, Line, BarChart, Bar, PieChart, Pie, Cell,
  XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, AreaChart, Area
} from "recharts";

type Page = "landing" | "chat" | "admin-login" | "admin";
type AdminPage = "dashboard" | "knowledge-base" | "logs" | "unanswered" | "feedback" | "analytics" | "settings" | "admin-users" | "activity-log";

// ─── Palette ────────────────────────────────────────────────────────────────
const NAVY = "#0C2340";
const OCEAN = "#1A5FA8";
const TEAL = "#0D9E8A";
const CYAN = "#38BDF8";
const LIGHT = "#F4F7FB";

// ─── Data ────────────────────────────────────────────────────────────────────
const questionTrend = Array.from({ length: 30 }, (_, i) => ({
  day: `${i + 1}`,
  pertanyaan: Math.floor(Math.random() * 60 + 20),
  dijawab: Math.floor(Math.random() * 50 + 15),
}));

const categoryData = [
  { name: "Persyaratan KP", value: 38 },
  { name: "Alur Pengajuan", value: 27 },
  { name: "Dokumen", value: 19 },
  { name: "Pelaksanaan", value: 11 },
  { name: "Sertifikat", value: 5 },
];

const statusData = [
  { name: "Berhasil", value: 72, color: TEAL },
  { name: "Tidak Ditemukan", value: 18, color: "#F59E0B" },
  { name: "Error", value: 10, color: "#EF4444" },
];

const feedbackData = Array.from({ length: 12 }, (_, i) => ({
  month: ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"][i],
  positif: Math.floor(Math.random() * 80 + 40),
  negatif: Math.floor(Math.random() * 20 + 5),
}));

const kbDocs = [
  { id: 1, name: "SOP Pelayanan KP dan Magang", category: "SOP", type: "PDF", version: "2.1", date: "2024-03-15", status: "Ready", chunks: 47, indexStatus: "Ready" },
  { id: 2, name: "Panduan Persyaratan Magang 2024", category: "Panduan", type: "DOCX", version: "1.3", date: "2024-02-20", status: "Ready", chunks: 23, indexStatus: "Ready" },
  { id: 3, name: "FAQ Kerja Praktik", category: "FAQ", type: "PDF", version: "1.0", date: "2024-04-01", status: "Ready", chunks: 18, indexStatus: "Ready" },
  { id: 4, name: "Alur Pengajuan Magang 2024", category: "Panduan", type: "PDF", version: "2.0", date: "2024-03-28", status: "Ready", chunks: 31, indexStatus: "Ready" },
  { id: 5, name: "Template Dokumen Pengajuan", category: "Template", type: "DOCX", version: "1.1", date: "2024-04-05", status: "Pending", chunks: 0, indexStatus: "Pending" },
  { id: 6, name: "Peraturan Dinas KP Jatim 2024", category: "Peraturan", type: "PDF", version: "1.0", date: "2024-04-10", status: "Processing", chunks: 0, indexStatus: "Processing" },
];

const convLogs = [
  { id: "C-001", question: "Apa saja persyaratan untuk mengajukan magang di DKP?", category: "Persyaratan", status: "Dijawab", sources: 3, score: "0.92", time: "1.2s", feedback: "Positif", date: "2024-04-10 14:32" },
  { id: "C-002", question: "Bagaimana alur pengajuan Kerja Praktik?", category: "Alur", status: "Dijawab", sources: 2, score: "0.88", time: "0.9s", feedback: "Positif", date: "2024-04-10 13:15" },
  { id: "C-003", question: "Apakah siswa SMK bisa magang di DKP Jatim?", category: "Umum", status: "Dijawab", sources: 1, score: "0.79", time: "1.5s", feedback: "Negatif", date: "2024-04-10 11:44" },
  { id: "C-004", question: "Berapa lama proses verifikasi dokumen pengajuan?", category: "Alur", status: "Tidak Ditemukan", sources: 0, score: "0.34", time: "2.1s", feedback: "-", date: "2024-04-10 10:02" },
  { id: "C-005", question: "Dokumen apa saja yang harus disiapkan untuk KP?", category: "Dokumen", status: "Dijawab", sources: 4, score: "0.95", time: "1.1s", feedback: "Positif", date: "2024-04-09 16:30" },
];

const unansweredList = [
  { id: 1, question: "Apakah ada kuota magang per semester?", freq: 12, category: "Kuota", score: "0.42", firstAsked: "2024-03-20", lastAsked: "2024-04-10", status: "Baru" },
  { id: 2, question: "Bagaimana prosedur jika pembimbing lapangan berhalangan?", freq: 8, category: "Pelaksanaan", score: "0.38", firstAsked: "2024-03-25", lastAsked: "2024-04-09", status: "Ditinjau" },
  { id: 3, question: "Apakah ada tunjangan untuk peserta magang?", freq: 6, category: "Tunjangan", score: "0.29", firstAsked: "2024-04-01", lastAsked: "2024-04-08", status: "Baru" },
  { id: 4, question: "Kapan jadwal penerimaan magang semester ganjil dibuka?", freq: 15, category: "Jadwal", score: "0.45", firstAsked: "2024-03-15", lastAsked: "2024-04-10", status: "Perlu Pembaruan KB" },
];

const feedbackList = [
  { id: 1, question: "Apa saja persyaratan pengajuan magang?", preview: "Berikut persyaratan yang dibutuhkan...", feedback: "Positif", reason: "-", date: "2024-04-10 14:32" },
  { id: 2, question: "Apakah siswa SMK dapat mengajukan magang?", preview: "Ya, siswa SMK dapat mengajukan...", feedback: "Negatif", reason: "Informasi Tidak Lengkap", date: "2024-04-10 11:44" },
  { id: 3, question: "Berapa lama proses pengajuan?", preview: "Proses pengajuan biasanya membutuhkan...", feedback: "Positif", reason: "-", date: "2024-04-09 09:20" },
  { id: 4, question: "Dokumen apa yang perlu disiapkan?", preview: "Dokumen yang perlu disiapkan antara lain...", feedback: "Negatif", reason: "Jawaban Sulit Dipahami", date: "2024-04-08 16:15" },
];

const adminUsers = [
  { id: 1, name: "Budi Santoso", email: "budi@dkp.jatimprov.go.id", role: "Super Admin", status: "Aktif", lastLogin: "2024-04-10 14:30", created: "2023-01-15" },
  { id: 2, name: "Siti Rahayu", email: "siti@dkp.jatimprov.go.id", role: "Admin", status: "Aktif", lastLogin: "2024-04-10 09:12", created: "2023-06-01" },
  { id: 3, name: "Ahmad Fauzi", email: "ahmad@dkp.jatimprov.go.id", role: "Admin", status: "Nonaktif", lastLogin: "2024-03-01 11:00", created: "2023-09-15" },
];

const activityLog = [
  { id: 1, admin: "Budi Santoso", action: "Upload", module: "Knowledge Base", desc: "Mengunggah dokumen SOP Pelayanan KP v2.1", date: "2024-04-10 14:30", ip: "192.168.1.10" },
  { id: 2, admin: "Siti Rahayu", action: "Edit", module: "Chatbot Settings", desc: "Memperbarui pesan sambutan chatbot", date: "2024-04-10 09:15", ip: "192.168.1.11" },
  { id: 3, admin: "Budi Santoso", action: "Delete", module: "Knowledge Base", desc: "Menghapus dokumen FAQ lama v0.9", date: "2024-04-09 16:45", ip: "192.168.1.10" },
  { id: 4, admin: "Siti Rahayu", action: "Login", module: "Auth", desc: "Login ke sistem admin", date: "2024-04-09 08:00", ip: "192.168.1.11" },
];

// ─── Shared UI ────────────────────────────────────────────────────────────────
function Badge({ children, color = "blue" }: { children: React.ReactNode; color?: string }) {
  const colors: Record<string, string> = {
    blue: "bg-blue-100 text-blue-700",
    green: "bg-emerald-100 text-emerald-700",
    yellow: "bg-amber-100 text-amber-700",
    red: "bg-red-100 text-red-700",
    gray: "bg-gray-100 text-gray-600",
    teal: "bg-teal-100 text-teal-700",
  };
  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colors[color] || colors.blue}`}>
      {children}
    </span>
  );
}

function StatusBadge({ status }: { status: string }) {
  const map: Record<string, { color: string; label: string }> = {
    Ready: { color: "green", label: "Siap" },
    Pending: { color: "yellow", label: "Menunggu" },
    Processing: { color: "blue", label: "Diproses" },
    Failed: { color: "red", label: "Gagal" },
    Dijawab: { color: "green", label: "Dijawab" },
    "Tidak Ditemukan": { color: "yellow", label: "Tidak Ditemukan" },
    Error: { color: "red", label: "Error" },
    Positif: { color: "green", label: "Positif" },
    Negatif: { color: "red", label: "Negatif" },
    Aktif: { color: "green", label: "Aktif" },
    Nonaktif: { color: "gray", label: "Nonaktif" },
    Baru: { color: "blue", label: "Baru" },
    Ditinjau: { color: "yellow", label: "Ditinjau" },
    "Perlu Pembaruan KB": { color: "red", label: "Perlu Update KB" },
    Selesai: { color: "green", label: "Selesai" },
    "Super Admin": { color: "teal", label: "Super Admin" },
    Admin: { color: "blue", label: "Admin" },
  };
  const conf = map[status] || { color: "gray", label: status };
  return <Badge color={conf.color}>{conf.label}</Badge>;
}

function MetricCard({ icon: Icon, label, value, sub, color = OCEAN }: {
  icon: React.ElementType; label: string; value: string; sub?: string; color?: string;
}) {
  return (
    <div className="bg-card rounded-2xl p-5 border border-border shadow-sm">
      <div className="flex items-start justify-between">
        <div>
          <p className="text-sm text-muted-foreground font-medium">{label}</p>
          <p className="text-2xl font-bold mt-1" style={{ color }}>{value}</p>
          {sub && <p className="text-xs text-muted-foreground mt-1">{sub}</p>}
        </div>
        <div className="p-2.5 rounded-xl" style={{ background: `${color}18` }}>
          <Icon size={20} style={{ color }} />
        </div>
      </div>
    </div>
  );
}

function SearchBar({ placeholder = "Cari..." }: { placeholder?: string }) {
  return (
    <div className="relative">
      <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
      <input
        className="pl-9 pr-4 py-2 rounded-xl bg-input-background border border-border text-sm w-full focus:outline-none focus:ring-2 focus:ring-primary/30"
        placeholder={placeholder}
      />
    </div>
  );
}

// ─── Logo ──────────────────────────────────────────────────────────────────────
function LogoMark({ size = 36, dark = false }: { size?: number; dark?: boolean }) {
  return (
    <div className="flex items-center gap-2.5">
      <div className="rounded-xl flex items-center justify-center" style={{ width: size, height: size, background: dark ? "rgba(255,255,255,0.12)" : OCEAN }}>
        <Fish size={size * 0.55} color={dark ? "#7EC8FF" : "#fff"} />
      </div>
      <div>
        <div className={`font-bold leading-tight text-sm ${dark ? "text-white" : "text-[#0C2340]"}`}>DKP Assistant</div>
        <div className={`text-[10px] leading-tight ${dark ? "text-blue-300" : "text-muted-foreground"}`}>Jawa Timur</div>
      </div>
    </div>
  );
}

// ─── LANDING PAGE ─────────────────────────────────────────────────────────────
function LandingPage({ onNavigate }: { onNavigate: (page: Page, q?: string) => void }) {
  const [mobileOpen, setMobileOpen] = useState(false);
  const [openFaq, setOpenFaq] = useState<number | null>(null);
  const faqs = [
    { q: "Apa itu DKP Assistant?", a: "DKP Assistant adalah layanan chatbot berbasis AI yang membantu mahasiswa dan siswa SMK menemukan informasi seputar Kerja Praktik dan Magang di Dinas Kelautan dan Perikanan Provinsi Jawa Timur." },
    { q: "Apakah jawaban chatbot merupakan keputusan resmi Dinas?", a: "Tidak. Jawaban yang diberikan DKP Assistant bersifat informatif dan berdasarkan dokumen resmi yang dikelola administrator. Untuk keputusan resmi, silakan hubungi petugas Dinas secara langsung." },
    { q: "Informasi apa saja yang dapat ditanyakan?", a: "Anda dapat menanyakan informasi seputar persyaratan KP & Magang, alur pengajuan, dokumen yang dibutuhkan, pelaksanaan kegiatan, penyelesaian, serta sertifikat dan administrasi akhir." },
    { q: "Dari mana chatbot mendapatkan informasi?", a: "DKP Assistant menggunakan teknologi Retrieval-Augmented Generation (RAG) yang mencari informasi dari knowledge base berisi dokumen resmi yang telah diverifikasi dan dikelola oleh administrator Dinas." },
    { q: "Apa yang terjadi jika chatbot tidak menemukan jawaban?", a: "Jika tidak ditemukan informasi yang relevan, DKP Assistant akan memberitahu Anda dan menyarankan untuk menghubungi petugas Dinas secara langsung. Pertanyaan tersebut juga akan dicatat untuk pembaruan knowledge base." },
    { q: "Bagaimana cara menghubungi petugas?", a: "Anda dapat menghubungi petugas Dinas Kelautan dan Perikanan Provinsi Jawa Timur melalui kontak resmi yang tertera di website resmi Dinas atau mengunjungi kantor langsung pada jam kerja." },
  ];

  const categories = [
    { icon: Shield, title: "Persyaratan KP & Magang", desc: "Informasi lengkap tentang syarat akademik, administrasi, dan kelengkapan dokumen.", example: "Apa saja persyaratan pengajuan magang?", color: OCEAN },
    { icon: ArrowRight, title: "Alur Pengajuan", desc: "Panduan langkah-langkah proses pengajuan dari awal hingga persetujuan.", example: "Bagaimana alur pengajuan KP?", color: TEAL },
    { icon: FileText, title: "Dokumen Pengajuan", desc: "Daftar dokumen yang wajib disiapkan dan cara pengumpulannya.", example: "Dokumen apa saja yang harus disiapkan?", color: "#6366F1" },
    { icon: BookOpen, title: "Pelaksanaan KP & Magang", desc: "Informasi tentang hak, kewajiban, dan tata tertib selama pelaksanaan.", example: "Apa kewajiban peserta magang selama pelaksanaan?", color: "#F59E0B" },
    { icon: CheckCircle, title: "Penyelesaian Kegiatan", desc: "Panduan pelaporan, evaluasi, dan prosedur mengakhiri kegiatan KP/Magang.", example: "Bagaimana prosedur penyelesaian KP?", color: "#EC4899" },
    { icon: Award, title: "Sertifikat & Administrasi Akhir", desc: "Informasi pengurusan sertifikat dan dokumen administratif setelah selesai.", example: "Bagaimana cara mendapatkan sertifikat magang?", color: "#0EA5E9" },
  ];

  const popularQs = [
    "Apa saja persyaratan pengajuan magang?",
    "Bagaimana alur pengajuan KP?",
    "Apakah siswa SMK dapat mengajukan magang?",
    "Berapa lama proses pengajuan?",
    "Dokumen apa saja yang harus disiapkan?",
    "Bagaimana jika dokumen pengajuan perlu diperbaiki?",
  ];

  return (
    <div className="min-h-screen bg-white font-[Inter,sans-serif]">
      {/* Navbar */}
      <nav className="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-border">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 rounded-xl flex items-center justify-center" style={{ background: OCEAN }}>
                <Fish size={20} color="#fff" />
              </div>
              <div>
                <div className="font-bold text-sm leading-tight" style={{ color: NAVY }}>Dinas Kelautan dan Perikanan</div>
                <div className="text-xs text-muted-foreground leading-tight">Provinsi Jawa Timur</div>
              </div>
            </div>
            <div className="hidden md:flex items-center gap-6">
              {["Beranda", "Layanan", "Informasi", "Cara Kerja", "FAQ", "Tentang"].map(item => (
                <a key={item} href="#" className="text-sm font-medium text-muted-foreground hover:text-[#1A5FA8] transition-colors">{item}</a>
              ))}
            </div>
            <div className="flex items-center gap-3">
              <button
                onClick={() => onNavigate("chat")}
                className="hidden md:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-95"
                style={{ background: OCEAN }}
              >
                <MessageSquare size={15} />
                Mulai Bertanya
              </button>
              <button className="md:hidden p-2" onClick={() => setMobileOpen(!mobileOpen)}>
                {mobileOpen ? <X size={20} /> : <Menu size={20} />}
              </button>
            </div>
          </div>
        </div>
        {mobileOpen && (
          <div className="md:hidden border-t border-border bg-white px-4 py-3 space-y-2">
            {["Beranda", "Layanan", "Cara Kerja", "FAQ"].map(item => (
              <a key={item} href="#" className="block py-2 text-sm font-medium text-muted-foreground">{item}</a>
            ))}
            <button onClick={() => onNavigate("chat")} className="w-full mt-2 py-2.5 rounded-xl text-sm font-semibold text-white" style={{ background: OCEAN }}>
              Mulai Bertanya
            </button>
          </div>
        )}
      </nav>

      {/* Hero */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12 lg:pt-20">
        <div className="grid lg:grid-cols-2 gap-12 items-center">
          <div>
            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold mb-6" style={{ background: `${OCEAN}15`, color: OCEAN }}>
              <Zap size={12} />
              Asisten Informasi KP & Magang Berbasis AI
            </span>
            <h1 className="text-4xl lg:text-5xl font-bold leading-tight mb-4" style={{ color: NAVY }}>
              Temukan Informasi Kerja Praktik dan Magang{" "}
              <span className="relative">
                <span style={{ color: OCEAN }}>Lebih Cepat</span>
                <svg className="absolute -bottom-1 left-0 w-full" height="4" viewBox="0 0 200 4" fill="none">
                  <path d="M0 2 Q100 0 200 2" stroke={TEAL} strokeWidth="2.5" strokeLinecap="round" fill="none" />
                </svg>
              </span>
            </h1>
            <p className="text-base text-muted-foreground leading-relaxed mb-8 max-w-lg">
              Tanyakan informasi seputar persyaratan, alur pengajuan, dokumen, pelaksanaan, dan layanan Kerja Praktik serta Magang melalui chatbot berbasis informasi resmi Dinas Kelautan dan Perikanan Provinsi Jawa Timur.
            </p>
            <div className="flex flex-wrap gap-3">
              <button
                onClick={() => onNavigate("chat")}
                className="flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold transition-all hover:opacity-90 active:scale-95 shadow-md"
                style={{ background: `linear-gradient(135deg, ${OCEAN}, ${NAVY})` }}
              >
                <MessageSquare size={17} />
                Mulai Bertanya
              </button>
              <button className="flex items-center gap-2 px-6 py-3 rounded-xl font-semibold border border-border text-foreground hover:bg-accent transition-all">
                Lihat Layanan
                <ChevronRight size={16} />
              </button>
            </div>
            {/* Feature badges */}
            <div className="flex flex-wrap gap-4 mt-10">
              {[
                { val: "24/7", desc: "Akses informasi kapan saja" },
                { val: "RAG", desc: "Jawaban berdasarkan dokumen resmi" },
                { val: "Transparan", desc: "Sumber jawaban dapat dilihat" },
              ].map(f => (
                <div key={f.val} className="flex items-center gap-2.5">
                  <div className="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xs text-white" style={{ background: OCEAN }}>{f.val}</div>
                  <span className="text-sm text-muted-foreground max-w-[120px]">{f.desc}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Chat Preview */}
          <div className="relative">
            <div className="rounded-2xl shadow-2xl border border-border bg-white overflow-hidden">
              <div className="px-4 py-3 flex items-center gap-2 border-b border-border" style={{ background: NAVY }}>
                <div className="w-7 h-7 rounded-lg flex items-center justify-center" style={{ background: "rgba(255,255,255,0.15)" }}>
                  <Fish size={14} color="#7EC8FF" />
                </div>
                <div>
                  <div className="text-white text-xs font-semibold">DKP Assistant</div>
                  <div className="flex items-center gap-1"><span className="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block" /><span className="text-blue-200 text-[10px]">Online</span></div>
                </div>
              </div>
              <div className="p-4 space-y-4 bg-[#F8FAFC]">
                <div className="flex justify-end">
                  <div className="max-w-[75%] bg-white rounded-2xl rounded-tr-sm px-4 py-2.5 shadow-sm border border-border text-sm text-foreground">
                    Apa saja persyaratan untuk mengajukan magang?
                  </div>
                </div>
                <div className="flex gap-2.5 items-start">
                  <div className="w-7 h-7 rounded-lg flex-shrink-0 flex items-center justify-center" style={{ background: OCEAN }}>
                    <Fish size={14} color="#fff" />
                  </div>
                  <div className="flex-1">
                    <div className="bg-white rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm border border-border text-sm text-foreground leading-relaxed">
                      Berikut persyaratan pengajuan magang berdasarkan informasi resmi yang tersedia:
                      <ol className="list-decimal pl-4 mt-2 space-y-1 text-muted-foreground">
                        <li>Surat permohonan dari institusi pendidikan</li>
                        <li>Fotokopi KTM / Kartu Siswa aktif</li>
                        <li>CV dan proposal kegiatan magang</li>
                        <li>Transkrip nilai terakhir</li>
                      </ol>
                    </div>
                    <div className="mt-2 flex items-center gap-1.5 px-3 py-2 rounded-xl border border-border bg-white text-xs text-muted-foreground">
                      <FileText size={12} style={{ color: TEAL }} />
                      <span className="font-medium">SOP Pelayanan KP & Magang</span>
                      <span>— Halaman 3</span>
                      <span className="ml-auto font-semibold" style={{ color: TEAL }}>92%</span>
                    </div>
                  </div>
                </div>
              </div>
              <div className="px-4 py-3 border-t border-border">
                <div className="flex items-center gap-2 px-3 py-2 rounded-xl border border-border bg-input-background text-sm text-muted-foreground">
                  <span className="flex-1">Tanyakan informasi tentang KP dan Magang...</span>
                  <div className="w-7 h-7 rounded-lg flex items-center justify-center" style={{ background: OCEAN }}>
                    <Send size={13} color="#fff" />
                  </div>
                </div>
              </div>
            </div>
            {/* decorative */}
            <div className="absolute -top-4 -right-4 w-20 h-20 rounded-full opacity-20" style={{ background: `radial-gradient(${CYAN}, transparent)` }} />
            <div className="absolute -bottom-4 -left-4 w-16 h-16 rounded-full opacity-15" style={{ background: `radial-gradient(${TEAL}, transparent)` }} />
          </div>
        </div>
      </section>

      {/* Categories */}
      <section className="py-16 bg-[#F4F7FB]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-10">
            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold mb-4" style={{ background: `${OCEAN}15`, color: OCEAN }}>
              Layanan Informasi
            </span>
            <h2 className="text-3xl font-bold mb-3" style={{ color: NAVY }}>Informasi yang Bisa Ditanyakan Melalui Chatbot</h2>
            <p className="text-muted-foreground max-w-xl mx-auto">Pilih kategori informasi Kerja Praktik dan Magang yang paling sering dibutuhkan.</p>
          </div>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            {categories.map(cat => (
              <button
                key={cat.title}
                onClick={() => onNavigate("chat", cat.example)}
                className="text-left p-5 bg-white rounded-2xl border border-border hover:shadow-md hover:border-[#1A5FA8]/30 transition-all group"
              >
                <div className="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style={{ background: `${cat.color}15` }}>
                  <cat.icon size={20} style={{ color: cat.color }} />
                </div>
                <h3 className="font-semibold text-sm mb-1.5" style={{ color: NAVY }}>{cat.title}</h3>
                <p className="text-xs text-muted-foreground leading-relaxed mb-3">{cat.desc}</p>
                <div className="text-xs italic text-muted-foreground border-l-2 pl-2 mb-3" style={{ borderColor: cat.color }}>"{cat.example}"</div>
                <span className="inline-flex items-center gap-1 text-xs font-semibold group-hover:gap-2 transition-all" style={{ color: cat.color }}>
                  Tanyakan ke Chatbot <ArrowRight size={12} />
                </span>
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Popular Questions */}
      <section className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-8">
            <h2 className="text-2xl font-bold" style={{ color: NAVY }}>Pertanyaan yang Sering Ditanyakan</h2>
          </div>
          <div className="flex flex-wrap justify-center gap-3">
            {popularQs.map(q => (
              <button
                key={q}
                onClick={() => onNavigate("chat", q)}
                className="px-4 py-2.5 rounded-xl border border-border bg-white text-sm text-foreground hover:border-[#1A5FA8]/40 hover:bg-[#F4F7FB] transition-all font-medium"
              >
                {q}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* How It Works */}
      <section className="py-16 bg-[#F4F7FB]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold mb-4" style={{ background: `${OCEAN}15`, color: OCEAN }}>
              Cara Kerja
            </span>
            <h2 className="text-3xl font-bold" style={{ color: NAVY }}>Bagaimana DKP Assistant Menghasilkan Jawaban?</h2>
          </div>
          <div className="flex flex-col lg:flex-row items-center gap-4 mb-8">
            {[
              { icon: MessageCircle, step: "01", title: "Pengguna Mengajukan Pertanyaan", color: OCEAN },
              { icon: Search, step: "02", title: "Sistem Mencari Informasi Relevan", color: "#6366F1" },
              { icon: Database, step: "03", title: "Dokumen Resmi Digunakan sebagai Konteks", color: TEAL },
              { icon: Zap, step: "04", title: "AI Menyusun Jawaban", color: "#F59E0B" },
              { icon: CheckCircle, step: "05", title: "Jawaban dan Sumber Ditampilkan", color: "#10B981" },
            ].map((s, i, arr) => (
              <div key={s.step} className="flex lg:flex-col items-center gap-3 lg:gap-2 flex-1">
                <div className="flex-shrink-0 flex flex-col lg:flex-row items-center gap-2 lg:gap-0 w-full lg:w-auto">
                  <div className="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm" style={{ background: s.color }}>
                    <s.icon size={22} color="#fff" />
                  </div>
                  {i < arr.length - 1 && (
                    <div className="hidden lg:block h-px flex-1 border-t-2 border-dashed border-border mx-3" style={{ minWidth: 20 }} />
                  )}
                </div>
                <div className="text-center lg:mt-3">
                  <div className="text-xs font-bold mb-0.5" style={{ color: s.color }}>Langkah {s.step}</div>
                  <div className="text-xs font-semibold" style={{ color: NAVY }}>{s.title}</div>
                </div>
              </div>
            ))}
          </div>
          <div className="max-w-2xl mx-auto p-4 rounded-2xl border border-[#1A5FA8]/20 bg-white flex items-start gap-3">
            <Info size={18} className="flex-shrink-0 mt-0.5" style={{ color: OCEAN }} />
            <p className="text-sm text-muted-foreground leading-relaxed">
              DKP Assistant hanya memberikan jawaban berdasarkan knowledge base yang telah diverifikasi dan dikelola oleh administrator.
            </p>
          </div>
        </div>
      </section>

      {/* Benefits */}
      <section className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-10">
            <h2 className="text-3xl font-bold" style={{ color: NAVY }}>Layanan Informasi yang Lebih Cepat dan Terstruktur</h2>
          </div>
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            {[
              { icon: Layers, title: "Informasi Terpusat", desc: "Informasi KP dan Magang tersedia dalam satu layanan yang mudah diakses.", color: OCEAN },
              { icon: FileCheck, title: "Jawaban Berbasis Dokumen", desc: "Jawaban dihasilkan berdasarkan sumber informasi resmi Dinas.", color: TEAL },
              { icon: RefreshCw, title: "Mudah Diperbarui", desc: "Administrator dapat memperbarui knowledge base kapan saja.", color: "#6366F1" },
              { icon: TrendingUp, title: "Evaluasi Layanan", desc: "Pertanyaan dan feedback pengguna membantu meningkatkan kualitas informasi.", color: "#F59E0B" },
            ].map(b => (
              <div key={b.title} className="p-5 rounded-2xl border border-border bg-[#F8FAFC]">
                <div className="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style={{ background: `${b.color}15` }}>
                  <b.icon size={20} style={{ color: b.color }} />
                </div>
                <h3 className="font-semibold text-sm mb-2" style={{ color: NAVY }}>{b.title}</h3>
                <p className="text-xs text-muted-foreground leading-relaxed">{b.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* FAQ */}
      <section className="py-16 bg-[#F4F7FB]">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-10">
            <h2 className="text-3xl font-bold" style={{ color: NAVY }}>Pertanyaan Umum</h2>
          </div>
          <div className="space-y-3">
            {faqs.map((faq, i) => (
              <div key={i} className="bg-white rounded-2xl border border-border overflow-hidden">
                <button
                  className="w-full flex items-center justify-between px-5 py-4 text-left"
                  onClick={() => setOpenFaq(openFaq === i ? null : i)}
                >
                  <span className="font-semibold text-sm" style={{ color: NAVY }}>{faq.q}</span>
                  {openFaq === i ? <ChevronUp size={16} className="flex-shrink-0 text-muted-foreground" /> : <ChevronDown size={16} className="flex-shrink-0 text-muted-foreground" />}
                </button>
                {openFaq === i && (
                  <div className="px-5 pb-4 text-sm text-muted-foreground leading-relaxed border-t border-border pt-3">{faq.a}</div>
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Banner */}
      <section className="py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="rounded-3xl p-12 text-center" style={{ background: `linear-gradient(135deg, ${NAVY} 0%, ${OCEAN} 100%)` }}>
            <h2 className="text-3xl font-bold text-white mb-3">Masih Memiliki Pertanyaan tentang KP atau Magang?</h2>
            <p className="text-blue-200 mb-8 max-w-lg mx-auto">Gunakan DKP Assistant untuk menemukan informasi berdasarkan dokumen resmi yang tersedia.</p>
            <button
              onClick={() => onNavigate("chat")}
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl font-semibold text-sm text-white border-2 border-white/30 hover:bg-white/10 transition-all"
            >
              <MessageSquare size={16} />
              Mulai Bertanya
            </button>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-border bg-white py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
            <div>
              <LogoMark />
              <p className="text-xs text-muted-foreground mt-3 leading-relaxed max-w-[180px]">
                Layanan chatbot AI untuk informasi Kerja Praktik dan Magang berbasis dokumen resmi.
              </p>
            </div>
            <div>
              <h4 className="font-semibold text-sm mb-3" style={{ color: NAVY }}>Navigasi</h4>
              {["Beranda", "Layanan", "Cara Kerja", "FAQ"].map(l => (
                <a key={l} href="#" className="block text-xs text-muted-foreground hover:text-foreground py-1">{l}</a>
              ))}
            </div>
            <div>
              <h4 className="font-semibold text-sm mb-3" style={{ color: NAVY }}>Akses</h4>
              {["Chatbot", "Kontak Layanan"].map(l => (
                <a key={l} href="#" className="block text-xs text-muted-foreground hover:text-foreground py-1">{l}</a>
              ))}
            </div>
            <div>
              <h4 className="font-semibold text-sm mb-3" style={{ color: NAVY }}>Instansi</h4>
              <p className="text-xs text-muted-foreground leading-relaxed">Dinas Kelautan dan Perikanan Provinsi Jawa Timur</p>
            </div>
          </div>
          <div className="border-t border-border pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p className="text-xs text-muted-foreground">© 2024 Dinas Kelautan dan Perikanan Provinsi Jawa Timur. Hak cipta dilindungi.</p>
            <div className="flex gap-4">
              {["Kebijakan Privasi", "Disclaimer"].map(l => (
                <a key={l} href="#" className="text-xs text-muted-foreground hover:text-foreground">{l}</a>
              ))}
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}

// ─── CHAT PAGE ────────────────────────────────────────────────────────────────
type ChatMsg = { role: "user" | "bot"; text: string; sources?: { title: string; cat: string; page: number; score: number }[]; ts: string };

function ChatPage({ onNavigate, initialQ }: { onNavigate: (p: Page) => void; initialQ?: string }) {
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [messages, setMessages] = useState<ChatMsg[]>([]);
  const [input, setInput] = useState(initialQ || "");
  const [typing, setTyping] = useState(false);
  const [copiedIdx, setCopiedIdx] = useState<number | null>(null);
  const endRef = useRef<HTMLDivElement>(null);

  const convHistory = [
    { label: "Hari Ini", items: ["Persyaratan magang 2024", "Alur pengajuan KP"] },
    { label: "Kemarin", items: ["Dokumen yang dibutuhkan"] },
    { label: "7 Hari Terakhir", items: ["FAQ Kerja Praktik", "Sertifikat magang"] },
  ];

  const suggested = [
    "Apa saja persyaratan pengajuan magang?",
    "Bagaimana alur pengajuan KP?",
    "Berapa lama proses pengajuan?",
    "Dokumen apa saja yang harus disiapkan?",
  ];

  useEffect(() => { endRef.current?.scrollIntoView({ behavior: "smooth" }); }, [messages, typing]);

  useEffect(() => {
    if (initialQ) {
      setTimeout(() => sendMsg(initialQ), 300);
    }
  }, []);

  const sendMsg = (text: string) => {
    if (!text.trim()) return;
    const userMsg: ChatMsg = { role: "user", text: text.trim(), ts: new Date().toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" }) };
    setMessages(prev => [...prev, userMsg]);
    setInput("");
    setTyping(true);
    setTimeout(() => {
      setTyping(false);
      const botMsg: ChatMsg = {
        role: "bot",
        text: `Berdasarkan dokumen resmi yang tersedia, berikut informasi mengenai "${text.trim()}":\n\nPersyaratan utama yang perlu dipenuhi meliputi kelengkapan dokumen administratif dari institusi pendidikan, proposal kegiatan yang telah disetujui oleh pembimbing, serta CV terbaru peserta. Seluruh dokumen diserahkan kepada bagian administrasi Dinas Kelautan dan Perikanan Provinsi Jawa Timur.`,
        sources: [
          { title: "SOP Pelayanan KP dan Magang", cat: "Persyaratan", page: 3, score: 92 },
          { title: "Panduan Persyaratan Magang 2024", cat: "Panduan", page: 7, score: 85 },
        ],
        ts: new Date().toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" }),
      };
      setMessages(prev => [...prev, botMsg]);
    }, 1800);
  };

  return (
    <div className="flex h-screen bg-[#F4F7FB] font-[Inter,sans-serif]">
      {/* Sidebar */}
      <div className={`flex flex-col transition-all duration-300 ${sidebarOpen ? "w-64" : "w-0"} overflow-hidden flex-shrink-0`} style={{ background: NAVY }}>
        <div className="p-4 border-b border-white/10">
          <div className="flex items-center justify-between">
            <LogoMark dark />
            <button onClick={() => setSidebarOpen(false)} className="p-1.5 rounded-lg hover:bg-white/10 text-blue-300 lg:hidden">
              <X size={15} />
            </button>
          </div>
        </div>
        <div className="p-3">
          <button className="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-white text-sm font-medium hover:bg-white/10 transition-all border border-white/15">
            <Plus size={15} /> Percakapan Baru
          </button>
        </div>
        <div className="flex-1 overflow-y-auto px-3 space-y-4 pb-4">
          {convHistory.map(group => (
            <div key={group.label}>
              <p className="text-[10px] font-semibold text-blue-400 uppercase tracking-wider px-2 mb-1">{group.label}</p>
              {group.items.map(item => (
                <button key={item} className="w-full text-left px-3 py-2 rounded-lg text-xs text-blue-200 hover:bg-white/8 hover:text-white transition-all truncate">
                  {item}
                </button>
              ))}
            </div>
          ))}
        </div>
        <div className="p-3 border-t border-white/10 space-y-1">
          {[
            { icon: Home, label: "Beranda" },
            { icon: MessageSquare, label: "Chatbot" },
            { icon: HelpCircle, label: "Bantuan" },
          ].map(item => (
            <button
              key={item.label}
              onClick={() => item.label === "Beranda" ? onNavigate("landing") : undefined}
              className="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs text-blue-300 hover:bg-white/10 hover:text-white transition-all"
            >
              <item.icon size={14} /> {item.label}
            </button>
          ))}
          <div className="pt-2 border-t border-white/10 space-y-1 text-[10px] text-blue-400 px-3">
            <a href="#" className="block hover:text-blue-200">Disclaimer</a>
            <a href="#" className="block hover:text-blue-200">Kebijakan Privasi</a>
          </div>
        </div>
      </div>

      {/* Main */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Top bar */}
        <div className="flex items-center gap-3 px-4 py-3 border-b border-border bg-white">
          {!sidebarOpen && (
            <button onClick={() => setSidebarOpen(true)} className="p-2 rounded-xl hover:bg-accent">
              <Menu size={18} />
            </button>
          )}
          <div className="flex items-center gap-2">
            <div className="w-7 h-7 rounded-lg flex items-center justify-center" style={{ background: OCEAN }}>
              <Fish size={14} color="#fff" />
            </div>
            <div>
              <div className="text-sm font-semibold" style={{ color: NAVY }}>DKP Assistant</div>
              <div className="flex items-center gap-1 text-[10px] text-muted-foreground"><span className="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block" />Online</div>
            </div>
          </div>
          <div className="ml-auto">
            <button onClick={() => onNavigate("landing")} className="flex items-center gap-1.5 text-xs text-muted-foreground hover:text-foreground">
              <ChevronLeft size={14} /> Beranda
            </button>
          </div>
        </div>

        {/* Messages */}
        <div className="flex-1 overflow-y-auto px-4 py-6">
          {messages.length === 0 && !typing ? (
            <div className="flex flex-col items-center justify-center h-full text-center">
              <div className="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 shadow-md" style={{ background: OCEAN }}>
                <Fish size={32} color="#fff" />
              </div>
              <h2 className="text-xl font-bold mb-2" style={{ color: NAVY }}>Halo, ada yang bisa DKP Assistant bantu?</h2>
              <p className="text-sm text-muted-foreground mb-8">Tanyakan informasi seputar Kerja Praktik dan Magang.</p>
              <div className="grid sm:grid-cols-2 gap-2.5 max-w-lg w-full">
                {suggested.map(q => (
                  <button
                    key={q}
                    onClick={() => { setInput(q); setTimeout(() => sendMsg(q), 0); }}
                    className="text-left px-4 py-3 rounded-xl border border-border bg-white text-sm text-foreground hover:border-[#1A5FA8]/30 hover:bg-[#F4F7FB] transition-all"
                  >
                    {q}
                  </button>
                ))}
              </div>
            </div>
          ) : (
            <div className="max-w-3xl mx-auto space-y-6">
              {messages.map((msg, i) => (
                <div key={i} className={`flex gap-3 ${msg.role === "user" ? "justify-end" : "items-start"}`}>
                  {msg.role === "bot" && (
                    <div className="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center" style={{ background: OCEAN }}>
                      <Fish size={16} color="#fff" />
                    </div>
                  )}
                  <div className={`max-w-[80%] ${msg.role === "user" ? "" : "flex-1"}`}>
                    {msg.role === "user" ? (
                      <div className="px-4 py-3 rounded-2xl rounded-tr-sm text-sm shadow-sm text-white" style={{ background: OCEAN }}>
                        {msg.text}
                      </div>
                    ) : (
                      <div>
                        <div className="bg-white rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm border border-border text-sm text-foreground leading-relaxed whitespace-pre-line">
                          {msg.text}
                        </div>
                        {msg.sources && (
                          <div className="mt-2 space-y-1.5">
                            {msg.sources.map((src, si) => (
                              <div key={si} className="flex items-center gap-2 px-3 py-2 rounded-xl border border-border bg-white text-xs text-muted-foreground">
                                <FileText size={12} style={{ color: TEAL }} />
                                <span className="font-medium text-foreground">{src.title}</span>
                                <Badge color="teal">{src.cat}</Badge>
                                <span>Hal. {src.page}</span>
                                <span className="ml-auto font-bold" style={{ color: TEAL }}>{src.score}%</span>
                                <button className="px-2 py-0.5 rounded-lg text-[10px] font-semibold border border-border hover:bg-accent">Lihat</button>
                              </div>
                            ))}
                          </div>
                        )}
                        <div className="mt-2 flex items-center gap-1">
                          {[
                            { icon: Copy, label: "Salin", action: () => { navigator.clipboard.writeText(msg.text); setCopiedIdx(i); setTimeout(() => setCopiedIdx(null), 1500); } },
                            { icon: RefreshCw, label: "Ulangi", action: () => {} },
                            { icon: ThumbsUp, label: "Membantu", action: () => {} },
                            { icon: ThumbsDown, label: "Tidak Membantu", action: () => {} },
                          ].map(btn => (
                            <button key={btn.label} onClick={btn.action} title={btn.label} className="p-1.5 rounded-lg hover:bg-accent text-muted-foreground hover:text-foreground transition-all">
                              <btn.icon size={13} color={btn.label === "Salin" && copiedIdx === i ? TEAL : undefined} />
                            </button>
                          ))}
                          <span className="ml-2 text-[10px] text-muted-foreground">{msg.ts}</span>
                        </div>
                      </div>
                    )}
                    {msg.role === "user" && <div className="text-[10px] text-muted-foreground mt-1 text-right">{msg.ts}</div>}
                  </div>
                </div>
              ))}
              {typing && (
                <div className="flex gap-3 items-start">
                  <div className="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center" style={{ background: OCEAN }}>
                    <Fish size={16} color="#fff" />
                  </div>
                  <div className="bg-white rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm border border-border">
                    <div className="flex items-center gap-1.5">
                      <div className="text-xs text-muted-foreground mr-2">Mencari informasi relevan...</div>
                      {[0, 1, 2].map(d => (
                        <span key={d} className="w-1.5 h-1.5 rounded-full animate-bounce" style={{ background: OCEAN, animationDelay: `${d * 150}ms` }} />
                      ))}
                    </div>
                  </div>
                </div>
              )}
              <div ref={endRef} />
            </div>
          )}
        </div>

        {/* Input */}
        <div className="border-t border-border bg-white px-4 py-3">
          <div className="max-w-3xl mx-auto">
            <div className="flex items-end gap-2 border border-border rounded-2xl bg-input-background px-4 py-3">
              <textarea
                value={input}
                onChange={e => setInput(e.target.value)}
                onKeyDown={e => { if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); sendMsg(input); } }}
                placeholder="Tanyakan informasi tentang KP dan Magang..."
                className="flex-1 resize-none bg-transparent text-sm outline-none max-h-32 min-h-[20px]"
                rows={1}
              />
              <div className="flex items-center gap-1.5 flex-shrink-0">
                <span className="text-[10px] text-muted-foreground">{input.length}/500</span>
                <button
                  onClick={() => sendMsg(input)}
                  disabled={!input.trim()}
                  className="w-8 h-8 rounded-xl flex items-center justify-center transition-all disabled:opacity-40"
                  style={{ background: OCEAN }}
                >
                  <Send size={14} color="#fff" />
                </button>
              </div>
            </div>
            <p className="text-[10px] text-muted-foreground text-center mt-2">
              DKP Assistant dapat menghasilkan jawaban yang kurang tepat. Pastikan kembali informasi penting melalui layanan resmi.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── ADMIN LOGIN ───────────────────────────────────────────────────────────────
function AdminLogin({ onLogin }: { onLogin: () => void }) {
  const [showPw, setShowPw] = useState(false);
  const [email, setEmail] = useState("admin@dkp.jatimprov.go.id");
  const [pw, setPw] = useState("password123");

  return (
    <div className="min-h-screen flex font-[Inter,sans-serif]" style={{ background: `linear-gradient(135deg, ${NAVY} 0%, ${OCEAN} 100%)` }}>
      <div className="m-auto w-full max-w-sm px-4">
        <div className="bg-white rounded-3xl shadow-2xl p-8">
          <div className="flex justify-center mb-6"><LogoMark size={44} /></div>
          <h1 className="text-xl font-bold text-center mb-1" style={{ color: NAVY }}>Masuk Admin</h1>
          <p className="text-xs text-center text-muted-foreground mb-6">Panel Manajemen DKP Assistant</p>

          <div className="p-3 rounded-xl border border-amber-200 bg-amber-50 flex items-start gap-2 mb-5">
            <Shield size={14} className="mt-0.5 flex-shrink-0 text-amber-600" />
            <p className="text-xs text-amber-700">Halaman ini hanya dapat diakses oleh administrator resmi.</p>
          </div>

          <div className="space-y-4">
            <div>
              <label className="block text-xs font-semibold mb-1.5" style={{ color: NAVY }}>Email</label>
              <input value={email} onChange={e => setEmail(e.target.value)} type="email" className="w-full px-3 py-2.5 rounded-xl border border-border bg-input-background text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
            </div>
            <div>
              <label className="block text-xs font-semibold mb-1.5" style={{ color: NAVY }}>Password</label>
              <div className="relative">
                <input value={pw} onChange={e => setPw(e.target.value)} type={showPw ? "text" : "password"} className="w-full px-3 py-2.5 pr-10 rounded-xl border border-border bg-input-background text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                <button onClick={() => setShowPw(!showPw)} className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                  {showPw ? <EyeOff size={15} /> : <Eye size={15} />}
                </button>
              </div>
            </div>
            <div className="flex items-center justify-between">
              <label className="flex items-center gap-2 text-xs text-muted-foreground cursor-pointer">
                <input type="checkbox" className="rounded" /> Ingat saya
              </label>
              <a href="#" className="text-xs font-medium" style={{ color: OCEAN }}>Lupa password?</a>
            </div>
            <button
              onClick={onLogin}
              className="w-full py-3 rounded-xl text-white font-semibold text-sm transition-all hover:opacity-90 active:scale-95"
              style={{ background: `linear-gradient(135deg, ${OCEAN}, ${NAVY})` }}
            >
              Masuk
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── ADMIN LAYOUT ─────────────────────────────────────────────────────────────
function AdminLayout({ currentPage, setPage, children, onLogout }: {
  currentPage: AdminPage; setPage: (p: AdminPage) => void; children: React.ReactNode; onLogout: () => void;
}) {
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const navItems: { icon: React.ElementType; label: string; page: AdminPage }[] = [
    { icon: BarChart2, label: "Dashboard", page: "dashboard" },
    { icon: Database, label: "Knowledge Base", page: "knowledge-base" },
    { icon: MessageSquare, label: "Conversation Logs", page: "logs" },
    { icon: Inbox, label: "Pertanyaan Tidak Terjawab", page: "unanswered" },
    { icon: ThumbsUp, label: "Feedback", page: "feedback" },
    { icon: TrendingUp, label: "Analytics", page: "analytics" },
    { icon: Settings, label: "Pengaturan Chatbot", page: "settings" },
    { icon: Users, label: "Manajemen Admin", page: "admin-users" },
    { icon: Activity, label: "Activity Log", page: "activity-log" },
  ];
  const pageTitles: Record<AdminPage, string> = {
    dashboard: "Dashboard", "knowledge-base": "Knowledge Base", logs: "Conversation Logs",
    unanswered: "Pertanyaan Tidak Terjawab", feedback: "Feedback Pengguna", analytics: "Analytics",
    settings: "Pengaturan Chatbot", "admin-users": "Manajemen Admin", "activity-log": "Activity Log",
  };

  return (
    <div className="flex h-screen bg-background font-[Inter,sans-serif]">
      {/* Sidebar */}
      <div className={`flex flex-col flex-shrink-0 transition-all duration-300 ${sidebarOpen ? "w-60" : "w-0"} overflow-hidden`} style={{ background: NAVY }}>
        <div className="p-4 border-b border-white/10">
          <LogoMark dark />
        </div>
        <nav className="flex-1 overflow-y-auto p-3 space-y-0.5">
          {navItems.map(item => (
            <button
              key={item.page}
              onClick={() => setPage(item.page)}
              className={`w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-medium transition-all ${currentPage === item.page ? "text-white" : "text-blue-300 hover:bg-white/8 hover:text-white"}`}
              style={currentPage === item.page ? { background: "rgba(26,95,168,0.5)" } : {}}
            >
              <item.icon size={15} />
              <span className="truncate">{item.label}</span>
            </button>
          ))}
        </nav>
        <div className="p-3 border-t border-white/10">
          <button onClick={onLogout} className="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs text-blue-300 hover:bg-white/8 hover:text-red-300 transition-all">
            <LogOut size={15} /> Logout
          </button>
        </div>
      </div>

      <div className="flex-1 flex flex-col min-w-0">
        {/* Top bar */}
        <div className="flex items-center gap-3 px-5 py-3 border-b border-border bg-white">
          <button onClick={() => setSidebarOpen(!sidebarOpen)} className="p-2 rounded-xl hover:bg-accent">
            <Menu size={16} />
          </button>
          <h1 className="text-sm font-semibold" style={{ color: NAVY }}>{pageTitles[currentPage]}</h1>
          <div className="ml-auto flex items-center gap-2">
            <div className="relative hidden sm:block w-48">
              <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
              <input className="pl-8 pr-3 py-2 rounded-xl bg-input-background border border-border text-xs w-full focus:outline-none" placeholder="Cari..." />
            </div>
            <button className="p-2 rounded-xl hover:bg-accent relative">
              <Bell size={16} />
              <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500" />
            </button>
            <div className="flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-input-background border border-border">
              <div className="w-6 h-6 rounded-lg flex items-center justify-center text-white text-[10px] font-bold" style={{ background: OCEAN }}>BS</div>
              <span className="text-xs font-medium hidden sm:block" style={{ color: NAVY }}>Budi Santoso</span>
            </div>
          </div>
        </div>
        <div className="flex-1 overflow-y-auto p-5">{children}</div>
      </div>
    </div>
  );
}

// ─── DASHBOARD ────────────────────────────────────────────────────────────────
function Dashboard({ setPage }: { setPage: (p: AdminPage) => void }) {
  return (
    <div className="space-y-6">
      <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <MetricCard icon={MessageSquare} label="Total Percakapan" value="1,284" sub="+12% bulan ini" color={OCEAN} />
        <MetricCard icon={Hash} label="Total Pertanyaan" value="3,571" sub="↑ 8% dari bulan lalu" color={TEAL} />
        <MetricCard icon={Activity} label="Pertanyaan Hari Ini" value="47" sub="Diperbarui real-time" color="#6366F1" />
        <MetricCard icon={Database} label="Knowledge Base Aktif" value="6" sub="2 dalam proses" color="#F59E0B" />
        <MetricCard icon={Inbox} label="Pertanyaan Tidak Terjawab" value="42" sub="4 baru hari ini" color="#EF4444" />
        <MetricCard icon={ThumbsUp} label="Feedback Positif" value="86%" sub="Dari 512 feedback" color={TEAL} />
        <MetricCard icon={Clock} label="Rata-rata Response Time" value="1.3s" sub="Stabil minggu ini" color={OCEAN} />
        <MetricCard icon={Star} label="Satisfaction Rate" value="4.2/5" sub="Berdasarkan rating" color="#F59E0B" />
      </div>

      <div className="grid lg:grid-cols-3 gap-5">
        <div className="lg:col-span-2 bg-card rounded-2xl border border-border p-5 shadow-sm">
          <h3 className="font-semibold text-sm mb-4" style={{ color: NAVY }}>Tren Pertanyaan Chatbot (30 Hari)</h3>
          <ResponsiveContainer width="100%" height={200}>
            <AreaChart data={questionTrend}>
              <defs>
                <linearGradient id="gradOcean" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor={OCEAN} stopOpacity={0.15} />
                  <stop offset="95%" stopColor={OCEAN} stopOpacity={0} />
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" stroke="#E4ECF6" />
              <XAxis dataKey="day" tick={{ fontSize: 10 }} interval={6} />
              <YAxis tick={{ fontSize: 10 }} />
              <Tooltip contentStyle={{ fontSize: 11, borderRadius: 8 }} />
              <Area type="monotone" dataKey="pertanyaan" stroke={OCEAN} fill="url(#gradOcean)" strokeWidth={2} name="Pertanyaan" />
              <Area type="monotone" dataKey="dijawab" stroke={TEAL} fill="none" strokeWidth={1.5} strokeDasharray="4 2" name="Dijawab" />
            </AreaChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-card rounded-2xl border border-border p-5 shadow-sm">
          <h3 className="font-semibold text-sm mb-4" style={{ color: NAVY }}>Status Jawaban</h3>
          <ResponsiveContainer width="100%" height={150}>
            <PieChart>
              <Pie data={statusData} cx="50%" cy="50%" innerRadius={45} outerRadius={65} paddingAngle={3} dataKey="value">
                {statusData.map((entry, i) => <Cell key={i} fill={entry.color} />)}
              </Pie>
              <Tooltip contentStyle={{ fontSize: 11, borderRadius: 8 }} />
            </PieChart>
          </ResponsiveContainer>
          <div className="space-y-1.5 mt-2">
            {statusData.map(d => (
              <div key={d.name} className="flex items-center justify-between text-xs">
                <div className="flex items-center gap-2"><span className="w-2.5 h-2.5 rounded-full" style={{ background: d.color }} />{d.name}</div>
                <span className="font-semibold">{d.value}%</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      <div className="grid lg:grid-cols-2 gap-5">
        <div className="bg-card rounded-2xl border border-border p-5 shadow-sm">
          <h3 className="font-semibold text-sm mb-4" style={{ color: NAVY }}>Kategori Pertanyaan Terbanyak</h3>
          <ResponsiveContainer width="100%" height={180}>
            <BarChart data={categoryData} layout="vertical">
              <CartesianGrid strokeDasharray="3 3" stroke="#E4ECF6" />
              <XAxis type="number" tick={{ fontSize: 10 }} />
              <YAxis dataKey="name" type="category" width={100} tick={{ fontSize: 10 }} />
              <Tooltip contentStyle={{ fontSize: 11, borderRadius: 8 }} />
              <Bar dataKey="value" fill={OCEAN} radius={[0, 4, 4, 0]} name="Pertanyaan" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-card rounded-2xl border border-border p-5 shadow-sm">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-semibold text-sm" style={{ color: NAVY }}>Pertanyaan Tidak Terjawab Terbaru</h3>
            <button onClick={() => setPage("unanswered")} className="text-xs font-medium" style={{ color: OCEAN }}>Lihat Semua →</button>
          </div>
          <div className="space-y-2.5">
            {unansweredList.slice(0, 3).map(u => (
              <div key={u.id} className="flex items-start gap-2 p-2.5 rounded-xl bg-[#F4F7FB] border border-border">
                <AlertCircle size={14} className="mt-0.5 flex-shrink-0 text-amber-500" />
                <div className="min-w-0">
                  <p className="text-xs font-medium truncate" style={{ color: NAVY }}>{u.question}</p>
                  <p className="text-[10px] text-muted-foreground mt-0.5">Ditanyakan {u.freq}x · <StatusBadge status={u.status} /></p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Recent Questions Table */}
      <div className="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
        <div className="px-5 py-4 border-b border-border">
          <h3 className="font-semibold text-sm" style={{ color: NAVY }}>Pertanyaan Terbaru</h3>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-xs">
            <thead><tr className="bg-[#F4F7FB] text-muted-foreground">
              {["Pertanyaan", "Kategori", "Status", "Waktu", "Feedback", "Aksi"].map(h => (
                <th key={h} className="text-left px-4 py-3 font-semibold">{h}</th>
              ))}
            </tr></thead>
            <tbody>
              {convLogs.map((row, i) => (
                <tr key={i} className="border-t border-border hover:bg-[#F8FAFC] transition-colors">
                  <td className="px-4 py-3 max-w-[200px]"><span className="truncate block">{row.question}</span></td>
                  <td className="px-4 py-3"><Badge color="blue">{row.category}</Badge></td>
                  <td className="px-4 py-3"><StatusBadge status={row.status} /></td>
                  <td className="px-4 py-3 text-muted-foreground">{row.time}</td>
                  <td className="px-4 py-3"><StatusBadge status={row.feedback} /></td>
                  <td className="px-4 py-3"><button className="p-1.5 rounded-lg hover:bg-accent"><Eye size={13} /></button></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

// ─── KNOWLEDGE BASE ───────────────────────────────────────────────────────────
function KnowledgeBase() {
  const [showUpload, setShowUpload] = useState(false);
  const [uploadStep, setUploadStep] = useState(0);
  const [dragging, setDragging] = useState(false);

  const steps = ["Mengunggah File", "Ekstraksi Teks", "Chunking Dokumen", "Membuat Embeddings", "Menyimpan ke Vektor DB", "Selesai"];

  const startUpload = () => {
    setUploadStep(0);
    const interval = setInterval(() => {
      setUploadStep(prev => {
        if (prev >= steps.length - 1) { clearInterval(interval); return prev; }
        return prev + 1;
      });
    }, 700);
  };

  return (
    <div className="space-y-5">
      <div className="grid sm:grid-cols-4 gap-4">
        <MetricCard icon={FileText} label="Total Dokumen" value="6" color={OCEAN} />
        <MetricCard icon={CheckCircle} label="Dokumen Aktif" value="4" color={TEAL} />
        <MetricCard icon={Layers} label="Total Chunks" value="119" color="#6366F1" />
        <MetricCard icon={XCircle} label="Gagal Diproses" value="0" color="#EF4444" />
      </div>

      <div className="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
        <div className="px-5 py-4 border-b border-border flex flex-wrap items-center gap-3">
          <h3 className="font-semibold text-sm flex-1" style={{ color: NAVY }}>Daftar Dokumen</h3>
          <SearchBar placeholder="Cari dokumen..." />
          <select className="px-3 py-2 rounded-xl border border-border bg-input-background text-xs">
            <option>Semua Kategori</option>
            <option>SOP</option><option>Panduan</option><option>FAQ</option><option>Template</option>
          </select>
          <select className="px-3 py-2 rounded-xl border border-border bg-input-background text-xs">
            <option>Semua Status</option>
            <option>Ready</option><option>Pending</option><option>Processing</option>
          </select>
          <button onClick={() => setShowUpload(true)} className="flex items-center gap-1.5 px-3 py-2 rounded-xl text-white text-xs font-semibold" style={{ background: OCEAN }}>
            <Plus size={13} /> Tambah Dokumen
          </button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-xs">
            <thead><tr className="bg-[#F4F7FB] text-muted-foreground">
              {["Nama Dokumen", "Kategori", "Tipe", "Versi", "Tgl Upload", "Status Indeks", "Chunks", "Status", "Aksi"].map(h => (
                <th key={h} className="text-left px-4 py-3 font-semibold whitespace-nowrap">{h}</th>
              ))}
            </tr></thead>
            <tbody>
              {kbDocs.map(doc => (
                <tr key={doc.id} className="border-t border-border hover:bg-[#F8FAFC] transition-colors">
                  <td className="px-4 py-3 font-medium max-w-[180px]"><span className="truncate block">{doc.name}</span></td>
                  <td className="px-4 py-3"><Badge color="blue">{doc.category}</Badge></td>
                  <td className="px-4 py-3 text-muted-foreground">{doc.type}</td>
                  <td className="px-4 py-3 text-muted-foreground">v{doc.version}</td>
                  <td className="px-4 py-3 text-muted-foreground">{doc.date}</td>
                  <td className="px-4 py-3"><StatusBadge status={doc.indexStatus} /></td>
                  <td className="px-4 py-3 text-muted-foreground">{doc.chunks || "-"}</td>
                  <td className="px-4 py-3"><StatusBadge status={doc.status} /></td>
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-1">
                      <button className="p-1.5 rounded-lg hover:bg-accent" title="Lihat"><Eye size={12} /></button>
                      <button className="p-1.5 rounded-lg hover:bg-accent" title="Edit"><Edit2 size={12} /></button>
                      <button className="p-1.5 rounded-lg hover:bg-accent" title="Re-index"><RotateCcw size={12} /></button>
                      <button className="p-1.5 rounded-lg hover:bg-accent text-red-500" title="Hapus"><Trash2 size={12} /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Upload Modal */}
      {showUpload && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <div className="flex items-center justify-between mb-5">
              <h3 className="font-bold text-base" style={{ color: NAVY }}>Upload Dokumen Knowledge Base</h3>
              <button onClick={() => { setShowUpload(false); setUploadStep(0); }} className="p-2 rounded-xl hover:bg-accent"><X size={16} /></button>
            </div>

            {uploadStep === 0 ? (
              <div className="space-y-4">
                <div
                  onDragOver={e => { e.preventDefault(); setDragging(true); }}
                  onDragLeave={() => setDragging(false)}
                  onDrop={e => { e.preventDefault(); setDragging(false); }}
                  className={`border-2 border-dashed rounded-2xl p-8 text-center transition-all ${dragging ? "border-[#1A5FA8] bg-[#F4F7FB]" : "border-border"}`}
                >
                  <Upload size={28} className="mx-auto mb-2 text-muted-foreground" />
                  <p className="text-sm font-medium mb-1" style={{ color: NAVY }}>Seret file ke sini atau klik untuk pilih</p>
                  <p className="text-xs text-muted-foreground">Mendukung: PDF, DOCX, XLSX (maks. 50MB)</p>
                </div>
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-semibold mb-1.5">Judul Dokumen</label>
                    <input className="w-full px-3 py-2 rounded-xl border border-border bg-input-background text-xs focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Judul dokumen..." />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold mb-1.5">Kategori</label>
                    <select className="w-full px-3 py-2 rounded-xl border border-border bg-input-background text-xs">
                      <option>SOP</option><option>Panduan</option><option>FAQ</option><option>Template</option><option>Peraturan</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-semibold mb-1.5">Versi</label>
                    <input className="w-full px-3 py-2 rounded-xl border border-border bg-input-background text-xs" placeholder="1.0" />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold mb-1.5">Tanggal Berlaku</label>
                    <input type="date" className="w-full px-3 py-2 rounded-xl border border-border bg-input-background text-xs" />
                  </div>
                </div>
                <div>
                  <label className="block text-xs font-semibold mb-1.5">Deskripsi</label>
                  <textarea className="w-full px-3 py-2 rounded-xl border border-border bg-input-background text-xs resize-none" rows={2} placeholder="Deskripsi singkat dokumen..." />
                </div>
                <button onClick={startUpload} className="w-full py-3 rounded-xl text-white text-sm font-semibold" style={{ background: OCEAN }}>
                  Upload dan Proses Dokumen
                </button>
              </div>
            ) : (
              <div className="space-y-3">
                {steps.map((step, i) => (
                  <div key={step} className="flex items-center gap-3">
                    <div className={`w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 ${i < uploadStep ? "bg-emerald-500" : i === uploadStep ? "" : "bg-gray-200"}`} style={i === uploadStep ? { background: OCEAN } : {}}>
                      {i < uploadStep ? <CheckCircle size={14} color="#fff" /> : i === uploadStep ? <div className="w-2 h-2 rounded-full bg-white animate-pulse" /> : null}
                    </div>
                    <span className={`text-xs font-medium ${i < uploadStep ? "text-emerald-600" : i === uploadStep ? "text-foreground" : "text-muted-foreground"}`}>{step}</span>
                    {i < uploadStep && <CheckCircle size={13} className="ml-auto text-emerald-500" />}
                  </div>
                ))}
                {uploadStep >= steps.length - 1 && (
                  <div className="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                    <p className="text-xs font-semibold text-emerald-700">Dokumen berhasil diproses dan siap digunakan!</p>
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}

// ─── CONVERSATION LOGS ────────────────────────────────────────────────────────
function ConversationLogs() {
  return (
    <div className="space-y-5">
      <div className="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
        <div className="px-5 py-4 border-b border-border flex flex-wrap gap-3 items-center">
          <h3 className="font-semibold text-sm flex-1" style={{ color: NAVY }}>Log Percakapan</h3>
          <SearchBar placeholder="Cari pertanyaan..." />
          <select className="px-3 py-2 rounded-xl border border-border bg-input-background text-xs"><option>Semua Status</option><option>Dijawab</option><option>Tidak Ditemukan</option><option>Error</option></select>
          <select className="px-3 py-2 rounded-xl border border-border bg-input-background text-xs"><option>Semua Kategori</option><option>Persyaratan</option><option>Alur</option><option>Dokumen</option></select>
          <select className="px-3 py-2 rounded-xl border border-border bg-input-background text-xs"><option>Semua Feedback</option><option>Positif</option><option>Negatif</option></select>
          <button className="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-border text-xs"><Download size={13} />Export</button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-xs">
            <thead><tr className="bg-[#F4F7FB] text-muted-foreground">
              {["ID", "Pertanyaan", "Kategori", "Status", "Sumber", "Score", "Waktu", "Feedback", "Tanggal", "Aksi"].map(h => (
                <th key={h} className="text-left px-4 py-3 font-semibold whitespace-nowrap">{h}</th>
              ))}
            </tr></thead>
            <tbody>
              {convLogs.map((row, i) => (
                <tr key={i} className="border-t border-border hover:bg-[#F8FAFC] transition-colors">
                  <td className="px-4 py-3 font-mono text-muted-foreground">{row.id}</td>
                  <td className="px-4 py-3 max-w-[180px]"><span className="truncate block">{row.question}</span></td>
                  <td className="px-4 py-3"><Badge color="blue">{row.category}</Badge></td>
                  <td className="px-4 py-3"><StatusBadge status={row.status} /></td>
                  <td className="px-4 py-3 text-muted-foreground">{row.sources} dok</td>
                  <td className="px-4 py-3 font-semibold" style={{ color: parseFloat(row.score) > 0.8 ? TEAL : "#F59E0B" }}>{row.score}</td>
                  <td className="px-4 py-3 text-muted-foreground">{row.time}</td>
                  <td className="px-4 py-3"><StatusBadge status={row.feedback} /></td>
                  <td className="px-4 py-3 text-muted-foreground whitespace-nowrap">{row.date}</td>
                  <td className="px-4 py-3"><button className="p-1.5 rounded-lg hover:bg-accent"><Eye size={12} /></button></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        <div className="px-5 py-3 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
          <span>Menampilkan 1-5 dari 1,284 percakapan</span>
          <div className="flex gap-1">
            {[1, 2, 3, "...", 257].map((p, i) => (
              <button key={i} className={`w-7 h-7 rounded-lg text-xs ${p === 1 ? "text-white" : "hover:bg-accent"}`} style={p === 1 ? { background: OCEAN } : {}}>{p}</button>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── UNANSWERED ───────────────────────────────────────────────────────────────
function UnansweredQuestions() {
  const [selected, setSelected] = useState<typeof unansweredList[0] | null>(null);

  return (
    <div className="space-y-5">
      <div className="grid sm:grid-cols-4 gap-4">
        <MetricCard icon={Inbox} label="Total Tidak Terjawab" value="42" color="#EF4444" />
        <MetricCard icon={AlertCircle} label="Belum Ditinjau" value="18" color="#F59E0B" />
        <MetricCard icon={Clock} label="Sedang Ditinjau" value="8" color={OCEAN} />
        <MetricCard icon={CheckCircle} label="Selesai" value="16" color={TEAL} />
      </div>

      <div className="flex gap-5">
        <div className="flex-1 bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
          <div className="px-5 py-4 border-b border-border flex flex-wrap gap-3 items-center">
            <h3 className="font-semibold text-sm flex-1" style={{ color: NAVY }}>Daftar Pertanyaan Tidak Terjawab</h3>
            <SearchBar placeholder="Cari pertanyaan..." />
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-xs">
              <thead><tr className="bg-[#F4F7FB] text-muted-foreground">
                {["Pertanyaan", "Frekuensi", "Kategori", "Score", "Pertama", "Terakhir", "Status", "Aksi"].map(h => (
                  <th key={h} className="text-left px-4 py-3 font-semibold whitespace-nowrap">{h}</th>
                ))}
              </tr></thead>
              <tbody>
                {unansweredList.map(row => (
                  <tr key={row.id} className="border-t border-border hover:bg-[#F8FAFC] transition-colors cursor-pointer" onClick={() => setSelected(row)}>
                    <td className="px-4 py-3 max-w-[200px]"><span className="truncate block">{row.question}</span></td>
                    <td className="px-4 py-3 font-semibold" style={{ color: OCEAN }}>{row.freq}x</td>
                    <td className="px-4 py-3"><Badge color="blue">{row.category}</Badge></td>
                    <td className="px-4 py-3 text-muted-foreground">{row.score}</td>
                    <td className="px-4 py-3 text-muted-foreground">{row.firstAsked}</td>
                    <td className="px-4 py-3 text-muted-foreground">{row.lastAsked}</td>
                    <td className="px-4 py-3"><StatusBadge status={row.status} /></td>
                    <td className="px-4 py-3"><button className="p-1.5 rounded-lg hover:bg-accent"><Eye size={12} /></button></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        {/* Detail Drawer */}
        {selected && (
          <div className="w-72 flex-shrink-0 bg-card rounded-2xl border border-border shadow-sm p-4 space-y-4">
            <div className="flex items-center justify-between">
              <h4 className="font-semibold text-sm" style={{ color: NAVY }}>Detail Pertanyaan</h4>
              <button onClick={() => setSelected(null)} className="p-1 rounded-lg hover:bg-accent"><X size={14} /></button>
            </div>
            <div className="p-3 rounded-xl bg-[#F4F7FB] border border-border">
              <p className="text-xs font-medium" style={{ color: NAVY }}>{selected.question}</p>
            </div>
            <div className="space-y-2 text-xs">
              <div className="flex justify-between"><span className="text-muted-foreground">Frekuensi</span><span className="font-semibold">{selected.freq}x ditanyakan</span></div>
              <div className="flex justify-between"><span className="text-muted-foreground">Kategori</span><Badge color="blue">{selected.category}</Badge></div>
              <div className="flex justify-between"><span className="text-muted-foreground">Status</span><StatusBadge status={selected.status} /></div>
              <div className="flex justify-between"><span className="text-muted-foreground">Score tertinggi</span><span className="font-semibold">{selected.score}</span></div>
            </div>
            <div>
              <p className="text-xs font-semibold mb-2" style={{ color: NAVY }}>Respons Fallback Chatbot</p>
              <p className="text-xs text-muted-foreground leading-relaxed p-2.5 rounded-lg bg-[#F4F7FB] border border-border italic">
                "Maaf, saya belum menemukan informasi yang cukup untuk menjawab pertanyaan tersebut berdasarkan dokumen yang tersedia."
              </p>
            </div>
            <div className="space-y-2">
              <button className="w-full px-3 py-2.5 rounded-xl text-xs font-semibold text-white" style={{ background: OCEAN }}>
                + Tambahkan Knowledge Base
              </button>
              <button className="w-full px-3 py-2.5 rounded-xl text-xs font-semibold border border-border hover:bg-accent">
                Hubungkan ke Dokumen
              </button>
              <button className="w-full px-3 py-2.5 rounded-xl text-xs font-semibold border border-emerald-300 text-emerald-700 hover:bg-emerald-50">
                Tandai Selesai
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

// ─── FEEDBACK ─────────────────────────────────────────────────────────────────
function Feedback() {
  return (
    <div className="space-y-5">
      <div className="grid sm:grid-cols-4 gap-4">
        <MetricCard icon={MessageSquare} label="Total Feedback" value="512" color={OCEAN} />
        <MetricCard icon={ThumbsUp} label="Feedback Positif" value="441" color={TEAL} />
        <MetricCard icon={ThumbsDown} label="Feedback Negatif" value="71" color="#EF4444" />
        <MetricCard icon={Star} label="Tingkat Kepuasan" value="86%" color="#F59E0B" />
      </div>

      <div className="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
        <div className="px-5 py-4 border-b border-border flex flex-wrap gap-3 items-center">
          <h3 className="font-semibold text-sm flex-1" style={{ color: NAVY }}>Daftar Feedback</h3>
          <select className="px-3 py-2 rounded-xl border border-border bg-input-background text-xs"><option>Semua Feedback</option><option>Positif</option><option>Negatif</option></select>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-xs">
            <thead><tr className="bg-[#F4F7FB] text-muted-foreground">
              {["Pertanyaan", "Pratinjau Jawaban", "Feedback", "Alasan", "Tanggal", "Aksi"].map(h => (
                <th key={h} className="text-left px-4 py-3 font-semibold">{h}</th>
              ))}
            </tr></thead>
            <tbody>
              {feedbackList.map(row => (
                <tr key={row.id} className="border-t border-border hover:bg-[#F8FAFC] transition-colors">
                  <td className="px-4 py-3 max-w-[200px]"><span className="truncate block font-medium">{row.question}</span></td>
                  <td className="px-4 py-3 max-w-[180px] text-muted-foreground"><span className="truncate block">{row.preview}</span></td>
                  <td className="px-4 py-3"><StatusBadge status={row.feedback} /></td>
                  <td className="px-4 py-3 text-muted-foreground">{row.reason || "-"}</td>
                  <td className="px-4 py-3 text-muted-foreground whitespace-nowrap">{row.date}</td>
                  <td className="px-4 py-3"><button className="p-1.5 rounded-lg hover:bg-accent"><Eye size={12} /></button></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

// ─── ANALYTICS ────────────────────────────────────────────────────────────────
function Analytics() {
  return (
    <div className="space-y-5">
      <div className="flex items-center gap-3 flex-wrap">
        <select className="px-3 py-2 rounded-xl border border-border bg-card text-xs shadow-sm"><option>30 Hari Terakhir</option><option>7 Hari Terakhir</option><option>90 Hari Terakhir</option></select>
        <select className="px-3 py-2 rounded-xl border border-border bg-card text-xs shadow-sm"><option>Semua Kategori</option></select>
        <button className="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-border bg-card text-xs shadow-sm"><Download size={12} />Export Laporan</button>
      </div>

      <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <MetricCard icon={MessageSquare} label="Total Percakapan" value="1,284" color={OCEAN} />
        <MetricCard icon={Hash} label="Total Pertanyaan" value="3,571" color={TEAL} />
        <MetricCard icon={Target} label="Answer Rate" value="82%" color="#6366F1" />
        <MetricCard icon={ThumbsUp} label="Positive Feedback Rate" value="86%" color="#F59E0B" />
      </div>

      <div className="grid lg:grid-cols-2 gap-5">
        <div className="bg-card rounded-2xl border border-border p-5 shadow-sm">
          <h3 className="font-semibold text-sm mb-4" style={{ color: NAVY }}>Pertanyaan dari Waktu ke Waktu</h3>
          <ResponsiveContainer width="100%" height={200}>
            <AreaChart data={questionTrend.slice(-14)}>
              <CartesianGrid strokeDasharray="3 3" stroke="#E4ECF6" />
              <XAxis dataKey="day" tick={{ fontSize: 10 }} />
              <YAxis tick={{ fontSize: 10 }} />
              <Tooltip contentStyle={{ fontSize: 11, borderRadius: 8 }} />
              <Area type="monotone" dataKey="pertanyaan" stroke={OCEAN} fill={`${OCEAN}20`} strokeWidth={2} name="Pertanyaan" />
            </AreaChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-card rounded-2xl border border-border p-5 shadow-sm">
          <h3 className="font-semibold text-sm mb-4" style={{ color: NAVY }}>Tren Feedback</h3>
          <ResponsiveContainer width="100%" height={200}>
            <BarChart data={feedbackData.slice(-8)}>
              <CartesianGrid strokeDasharray="3 3" stroke="#E4ECF6" />
              <XAxis dataKey="month" tick={{ fontSize: 10 }} />
              <YAxis tick={{ fontSize: 10 }} />
              <Tooltip contentStyle={{ fontSize: 11, borderRadius: 8 }} />
              <Legend wrapperStyle={{ fontSize: 11 }} />
              <Bar dataKey="positif" fill={TEAL} radius={[4, 4, 0, 0]} name="Positif" />
              <Bar dataKey="negatif" fill="#EF4444" radius={[4, 4, 0, 0]} name="Negatif" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-card rounded-2xl border border-border p-5 shadow-sm">
          <h3 className="font-semibold text-sm mb-4" style={{ color: NAVY }}>Kategori Pertanyaan Terbanyak</h3>
          <ResponsiveContainer width="100%" height={200}>
            <BarChart data={categoryData} layout="vertical">
              <CartesianGrid strokeDasharray="3 3" stroke="#E4ECF6" />
              <XAxis type="number" tick={{ fontSize: 10 }} />
              <YAxis dataKey="name" type="category" width={110} tick={{ fontSize: 10 }} />
              <Tooltip contentStyle={{ fontSize: 11, borderRadius: 8 }} />
              <Bar dataKey="value" fill={OCEAN} radius={[0, 4, 4, 0]} name="Pertanyaan" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-card rounded-2xl border border-border p-5 shadow-sm">
          <h3 className="font-semibold text-sm mb-4" style={{ color: NAVY }}>Penggunaan Knowledge Base (Top Sumber)</h3>
          <div className="space-y-2.5">
            {kbDocs.filter(d => d.chunks > 0).map(doc => (
              <div key={doc.id} className="space-y-1">
                <div className="flex justify-between text-xs">
                  <span className="font-medium truncate max-w-[200px]" style={{ color: NAVY }}>{doc.name}</span>
                  <span className="text-muted-foreground">{Math.floor(Math.random() * 80 + 20)}%</span>
                </div>
                <div className="h-1.5 rounded-full bg-[#E4ECF6] overflow-hidden">
                  <div className="h-full rounded-full" style={{ width: `${Math.floor(Math.random() * 80 + 20)}%`, background: OCEAN }} />
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── SETTINGS ─────────────────────────────────────────────────────────────────
function ChatbotSettings() {
  const [tab, setTab] = useState<"general" | "questions" | "fallback" | "rag" | "prompt">("general");
  const [enabled, setEnabled] = useState(true);
  const [showSource, setShowSource] = useState(true);
  const [topK, setTopK] = useState(5);
  const [threshold, setThreshold] = useState(0.7);

  const tabs = [
    { id: "general", label: "Umum" },
    { id: "questions", label: "Pertanyaan Saran" },
    { id: "fallback", label: "Fallback" },
    { id: "rag", label: "Konfigurasi RAG" },
    { id: "prompt", label: "Prompt" },
  ] as const;

  return (
    <div className="space-y-5">
      <div className="flex gap-1 p-1 rounded-xl bg-card border border-border shadow-sm w-fit">
        {tabs.map(t => (
          <button
            key={t.id}
            onClick={() => setTab(t.id)}
            className={`px-4 py-2 rounded-lg text-xs font-semibold transition-all ${tab === t.id ? "text-white shadow-sm" : "text-muted-foreground hover:text-foreground"}`}
            style={tab === t.id ? { background: OCEAN } : {}}
          >
            {t.label}
          </button>
        ))}
      </div>

      <div className="bg-card rounded-2xl border border-border shadow-sm p-6 max-w-2xl space-y-5">
        {tab === "general" && (
          <>
            <div className="flex items-center justify-between p-3 rounded-xl bg-[#F4F7FB] border border-border">
              <div>
                <p className="text-sm font-semibold" style={{ color: NAVY }}>Status Chatbot</p>
                <p className="text-xs text-muted-foreground">Aktifkan atau nonaktifkan layanan chatbot</p>
              </div>
              <button onClick={() => setEnabled(!enabled)} className={`w-12 h-6 rounded-full transition-all ${enabled ? "" : "bg-gray-300"}`} style={enabled ? { background: TEAL } : {}}>
                <div className={`w-5 h-5 bg-white rounded-full shadow transition-all ${enabled ? "translate-x-6" : "translate-x-0.5"} translate-y-0.5`} />
              </button>
            </div>
            {[
              { label: "Nama Chatbot", val: "DKP Assistant" },
              { label: "Deskripsi Chatbot", val: "Asisten informasi KP & Magang DKP Jawa Timur" },
            ].map(f => (
              <div key={f.label}>
                <label className="block text-xs font-semibold mb-1.5">{f.label}</label>
                <input defaultValue={f.val} className="w-full px-3 py-2.5 rounded-xl border border-border bg-input-background text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" />
              </div>
            ))}
            <div>
              <label className="block text-xs font-semibold mb-1.5">Pesan Sambutan</label>
              <textarea defaultValue="Halo! Saya DKP Assistant. Ada yang bisa saya bantu seputar Kerja Praktik dan Magang?" className="w-full px-3 py-2.5 rounded-xl border border-border bg-input-background text-sm resize-none focus:outline-none focus:ring-2 focus:ring-primary/20" rows={3} />
            </div>
            <button className="px-5 py-2.5 rounded-xl text-white text-sm font-semibold" style={{ background: OCEAN }}>Simpan Perubahan</button>
          </>
        )}

        {tab === "rag" && (
          <>
            <div>
              <label className="block text-xs font-semibold mb-2">Retrieval Top-K: <span style={{ color: OCEAN }}>{topK}</span></label>
              <input type="range" min={1} max={10} value={topK} onChange={e => setTopK(+e.target.value)} className="w-full accent-[#1A5FA8]" />
              <div className="flex justify-between text-[10px] text-muted-foreground"><span>1</span><span>10</span></div>
            </div>
            <div>
              <label className="block text-xs font-semibold mb-2">Similarity Threshold: <span style={{ color: OCEAN }}>{threshold.toFixed(2)}</span></label>
              <input type="range" min={0.5} max={0.99} step={0.01} value={threshold} onChange={e => setThreshold(+e.target.value)} className="w-full accent-[#1A5FA8]" />
            </div>
            <div>
              <label className="block text-xs font-semibold mb-1.5">Maximum Context Length (tokens)</label>
              <input defaultValue="4096" className="w-full px-3 py-2.5 rounded-xl border border-border bg-input-background text-sm focus:outline-none" />
            </div>
            <div className="flex items-center justify-between p-3 rounded-xl bg-[#F4F7FB] border border-border">
              <div>
                <p className="text-sm font-semibold" style={{ color: NAVY }}>Tampilkan Kutipan Sumber</p>
                <p className="text-xs text-muted-foreground">Tampilkan sumber dokumen pada setiap jawaban</p>
              </div>
              <button onClick={() => setShowSource(!showSource)} className="w-12 h-6 rounded-full transition-all" style={{ background: showSource ? TEAL : "#CBD5E1" }}>
                <div className={`w-5 h-5 bg-white rounded-full shadow transition-all ${showSource ? "translate-x-6" : "translate-x-0.5"} translate-y-0.5`} />
              </button>
            </div>
            <button className="px-5 py-2.5 rounded-xl text-white text-sm font-semibold" style={{ background: OCEAN }}>Simpan Perubahan</button>
          </>
        )}

        {tab === "prompt" && (
          <>
            <div>
              <label className="block text-xs font-semibold mb-1.5">System Instruction</label>
              <textarea defaultValue={`Anda adalah DKP Assistant, asisten AI yang membantu pengguna mendapatkan informasi seputar Kerja Praktik (KP) dan Magang di Dinas Kelautan dan Perikanan Provinsi Jawa Timur.\n\nAnda hanya menjawab berdasarkan konteks dokumen resmi yang diberikan. Jika informasi tidak tersedia dalam konteks, sampaikan bahwa informasi belum tersedia dan sarankan pengguna untuk menghubungi petugas Dinas.`} className="w-full px-3 py-2.5 rounded-xl border border-border bg-input-background text-sm resize-none focus:outline-none font-mono text-xs" rows={8} />
            </div>
            <div className="flex gap-3">
              <button className="px-5 py-2.5 rounded-xl text-white text-sm font-semibold" style={{ background: OCEAN }}>Simpan</button>
              <button className="px-5 py-2.5 rounded-xl text-sm font-semibold border border-border hover:bg-accent">Reset ke Default</button>
            </div>
          </>
        )}

        {(tab === "questions" || tab === "fallback") && (
          <div className="text-center py-8 text-muted-foreground text-sm">
            <Settings size={32} className="mx-auto mb-2 opacity-30" />
            Konfigurasi {tab === "questions" ? "pertanyaan saran" : "fallback"} tersedia di sini.
          </div>
        )}
      </div>
    </div>
  );
}

// ─── ADMIN USERS ──────────────────────────────────────────────────────────────
function AdminUsers() {
  return (
    <div className="space-y-5">
      <div className="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
        <div className="px-5 py-4 border-b border-border flex items-center gap-3">
          <h3 className="font-semibold text-sm flex-1" style={{ color: NAVY }}>Daftar Administrator</h3>
          <button className="flex items-center gap-1.5 px-3 py-2 rounded-xl text-white text-xs font-semibold" style={{ background: OCEAN }}>
            <Plus size={13} /> Tambah Admin
          </button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-xs">
            <thead><tr className="bg-[#F4F7FB] text-muted-foreground">
              {["Nama", "Email", "Role", "Status", "Login Terakhir", "Dibuat", "Aksi"].map(h => (
                <th key={h} className="text-left px-4 py-3 font-semibold">{h}</th>
              ))}
            </tr></thead>
            <tbody>
              {adminUsers.map(u => (
                <tr key={u.id} className="border-t border-border hover:bg-[#F8FAFC] transition-colors">
                  <td className="px-4 py-3 font-medium">{u.name}</td>
                  <td className="px-4 py-3 text-muted-foreground">{u.email}</td>
                  <td className="px-4 py-3"><StatusBadge status={u.role} /></td>
                  <td className="px-4 py-3"><StatusBadge status={u.status} /></td>
                  <td className="px-4 py-3 text-muted-foreground">{u.lastLogin}</td>
                  <td className="px-4 py-3 text-muted-foreground">{u.created}</td>
                  <td className="px-4 py-3">
                    <div className="flex gap-1">
                      <button className="p-1.5 rounded-lg hover:bg-accent"><Edit2 size={12} /></button>
                      <button className="p-1.5 rounded-lg hover:bg-accent"><Lock size={12} /></button>
                      <button className="p-1.5 rounded-lg hover:bg-accent text-red-500"><Trash2 size={12} /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

// ─── ACTIVITY LOG ─────────────────────────────────────────────────────────────
function ActivityLog() {
  return (
    <div className="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
      <div className="px-5 py-4 border-b border-border flex flex-wrap gap-3 items-center">
        <h3 className="font-semibold text-sm flex-1" style={{ color: NAVY }}>Log Aktivitas Administrator</h3>
        <SearchBar placeholder="Cari aktivitas..." />
        <select className="px-3 py-2 rounded-xl border border-border bg-input-background text-xs"><option>Semua Admin</option></select>
        <select className="px-3 py-2 rounded-xl border border-border bg-input-background text-xs"><option>Semua Modul</option></select>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full text-xs">
          <thead><tr className="bg-[#F4F7FB] text-muted-foreground">
            {["Administrator", "Aksi", "Modul", "Deskripsi", "Tanggal & Waktu", "IP Address"].map(h => (
              <th key={h} className="text-left px-4 py-3 font-semibold whitespace-nowrap">{h}</th>
            ))}
          </tr></thead>
          <tbody>
            {activityLog.map(row => (
              <tr key={row.id} className="border-t border-border hover:bg-[#F8FAFC] transition-colors">
                <td className="px-4 py-3 font-medium">{row.admin}</td>
                <td className="px-4 py-3">
                  <Badge color={row.action === "Delete" ? "red" : row.action === "Upload" ? "teal" : "blue"}>{row.action}</Badge>
                </td>
                <td className="px-4 py-3 text-muted-foreground">{row.module}</td>
                <td className="px-4 py-3 max-w-[250px]"><span className="truncate block">{row.desc}</span></td>
                <td className="px-4 py-3 text-muted-foreground whitespace-nowrap">{row.date}</td>
                <td className="px-4 py-3 font-mono text-muted-foreground">{row.ip}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

// ─── ADMIN PAGES ROUTER ───────────────────────────────────────────────────────
function AdminPages({ page, setPage }: { page: AdminPage; setPage: (p: AdminPage) => void }) {
  switch (page) {
    case "dashboard": return <Dashboard setPage={setPage} />;
    case "knowledge-base": return <KnowledgeBase />;
    case "logs": return <ConversationLogs />;
    case "unanswered": return <UnansweredQuestions />;
    case "feedback": return <Feedback />;
    case "analytics": return <Analytics />;
    case "settings": return <ChatbotSettings />;
    case "admin-users": return <AdminUsers />;
    case "activity-log": return <ActivityLog />;
    default: return <Dashboard setPage={setPage} />;
  }
}

// ─── APP ROOT ─────────────────────────────────────────────────────────────────
export default function App() {
  const [page, setPage] = useState<Page>("landing");
  const [adminPage, setAdminPage] = useState<AdminPage>("dashboard");
  const [initialQ, setInitialQ] = useState<string | undefined>(undefined);

  const navigateTo = (p: Page, q?: string) => {
    setInitialQ(q);
    setPage(p);
  };

  const NavPill = () => (
    <div className="fixed bottom-5 left-1/2 -translate-x-1/2 z-[999] flex gap-1 p-1.5 rounded-2xl shadow-xl border border-border bg-white/95 backdrop-blur text-[11px] font-semibold">
      {([
        { label: "Landing", p: "landing" as Page },
        { label: "Chat", p: "chat" as Page },
        { label: "Admin Login", p: "admin-login" as Page },
        { label: "Dashboard", p: "admin" as Page },
      ]).map(item => (
        <button
          key={item.label}
          onClick={() => { if (item.p === "chat") setInitialQ(undefined); setPage(item.p); }}
          className="px-3 py-1.5 rounded-xl transition-all"
          style={page === item.p ? { background: NAVY, color: "#fff" } : { color: "#5B7A9D" }}
        >
          {item.label}
        </button>
      ))}
    </div>
  );

  if (page === "landing") return <><LandingPage onNavigate={navigateTo} /><NavPill /></>;
  if (page === "chat") return <><ChatPage onNavigate={p => setPage(p)} initialQ={initialQ} /><NavPill /></>;
  if (page === "admin-login") return <><AdminLogin onLogin={() => setPage("admin")} /><NavPill /></>;
  if (page === "admin") return (
    <>
      <AdminLayout currentPage={adminPage} setPage={setAdminPage} onLogout={() => setPage("landing")}>
        <AdminPages page={adminPage} setPage={setAdminPage} />
      </AdminLayout>
      <NavPill />
    </>
  );

  return <LandingPage onNavigate={navigateTo} />;
}
