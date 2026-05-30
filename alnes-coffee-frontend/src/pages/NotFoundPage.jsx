import { useNavigate } from 'react-router-dom'

export default function NotFoundPage() {
  const navigate = useNavigate()

  return (
    <div className="min-h-screen bg-[#FAFAF8] flex flex-col items-center justify-center px-6 text-center relative overflow-hidden">

      {/* Background decorative circles */}
      <div className="absolute top-[-80px] right-[-80px] w-64 h-64 rounded-full bg-[#F0EDE8] opacity-60" />
      <div className="absolute bottom-[-60px] left-[-60px] w-48 h-48 rounded-full bg-[#E5DDD3] opacity-40" />
      <div className="absolute top-1/3 left-[-30px] w-20 h-20 rounded-full bg-[#F0EDE8] opacity-50" />

      {/* Main content */}
      <div className="relative z-10 flex flex-col items-center">

        {/* Icon illustration */}
        <div className="relative mb-8">
          {/* Outer ring */}
          <div className="w-36 h-36 rounded-full bg-gradient-to-br from-[#F0EDE8] to-[#E5DDD3] flex items-center justify-center shadow-inner">
            {/* Inner circle */}
            <div className="w-24 h-24 rounded-full bg-white flex items-center justify-center shadow-sm">
              <svg viewBox="0 0 64 64" className="w-14 h-14" fill="none" xmlns="http://www.w3.org/2000/svg">
                {/* Cup body */}
                <path d="M14 26h28l-3.5 18H17.5L14 26z" fill="#1C1008" rx="2" />
                {/* Cup rim */}
                <rect x="13" y="23" width="30" height="4" rx="2" fill="#2C1A0E" />
                {/* Handle */}
                <path d="M42 30h5a5 5 0 010 10h-5" stroke="#1C1008" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                {/* Saucer */}
                <ellipse cx="28" cy="46" rx="16" ry="2.5" fill="#5C4A35" opacity="0.25" />
                {/* Steam */}
                <path d="M22 20c1-2 1-4 0-6" stroke="#C4A882" strokeWidth="2" strokeLinecap="round" />
                <path d="M28 19c1-2 1-4 0-6" stroke="#C4A882" strokeWidth="2" strokeLinecap="round" />
                <path d="M34 20c1-2 1-4 0-6" stroke="#C4A882" strokeWidth="2" strokeLinecap="round" />
                {/* Question mark */}
                <text x="21" y="41" fontSize="13" fontWeight="800" fill="white" fontFamily="sans-serif">?</text>
              </svg>
            </div>
          </div>

          {/* Floating dots */}
          <div className="absolute top-1 right-0 w-4 h-4 rounded-full bg-[#C4A882] opacity-70 animate-bounce" style={{ animationDelay: '0s', animationDuration: '2s' }} />
          <div className="absolute bottom-2 left-0 w-3 h-3 rounded-full bg-[#E5DDD3]" />
          <div className="absolute top-6 left-[-8px] w-2 h-2 rounded-full bg-[#C4A882] opacity-50" />
          <div className="absolute bottom-6 right-[-6px] w-2.5 h-2.5 rounded-full bg-[#5C4A35] opacity-30" />
        </div>

        {/* 404 badge */}
        <div className="inline-flex items-center gap-1.5 bg-[#1C1008] text-white text-xs font-bold px-3 py-1 rounded-full mb-4 tracking-wider">
          <span>404</span>
          <span className="opacity-40">•</span>
          <span className="font-normal opacity-80">NOT FOUND</span>
        </div>

        {/* Heading */}
        <h1 className="text-2xl font-bold text-[#1C1008] mb-3 leading-tight">
          Kopimu Tersesat! ☕
        </h1>

        {/* Description */}
        <p className="text-[#8B7355] text-sm leading-relaxed max-w-[260px]">
          Halaman ini tidak tersedia. Yuk kembali dan scan QR Code di mejamu untuk mulai memesan.
        </p>

        {/* Divider */}
        <div className="flex items-center gap-3 my-7 w-full max-w-[280px]">
          <div className="flex-1 h-px bg-[#E5DDD3]" />
          <div className="w-1.5 h-1.5 rounded-full bg-[#C4A882]" />
          <div className="flex-1 h-px bg-[#E5DDD3]" />
        </div>

        {/* QR hint card */}
        <div className="bg-white border border-[#E5DDD3] rounded-2xl px-5 py-4 w-full max-w-[280px] shadow-sm mb-4">
          <div className="flex items-center gap-3">
            <div className="w-11 h-11 rounded-xl bg-[#1C1008] flex items-center justify-center flex-shrink-0">
              <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
              </svg>
            </div>
            <div className="text-left">
              <p className="text-xs font-bold text-[#1C1008]">Scan QR Code</p>
              <p className="text-xs text-[#8B7355] mt-0.5">Tersedia di setiap meja café</p>
            </div>
          </div>
        </div>

        {/* Footer note */}
        <p className="text-xs text-[#B5A898] mt-2">
          Alnes Coffee and Venue Batu
        </p>

      </div>
    </div>
  )
}