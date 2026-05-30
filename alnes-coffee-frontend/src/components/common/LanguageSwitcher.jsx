import { useTranslation } from 'react-i18next'

export default function LanguageSwitcher() {
  const { i18n } = useTranslation()

  const toggle = () => {
    const next = i18n.language === 'id' ? 'en' : 'id'
    i18n.changeLanguage(next)
    localStorage.setItem('lang', next)
  }

  const isID = i18n.language === 'id'

  return (
    <button
      onClick={toggle}
      className="flex items-center gap-1.5 px-3 py-1.5 rounded-full transition-all active:scale-95"
      style={{
        background: '#EDE8E0',
        color: '#1C1008',
        fontSize: 11,
        fontWeight: 600,
        letterSpacing: '0.05em',
      }}
    >
      {/* Bulatan warna bendera */}
      <span
        className="w-3.5 h-3.5 rounded-full flex-shrink-0"
        style={{
          background: isID
            ? 'linear-gradient(to bottom, #cc0001 50%, #fff 50%)'
            : 'linear-gradient(to bottom, #012169 33%, #fff 33%, #fff 66%, #cc0001 66%)',
          border: '1px solid rgba(0,0,0,0.1)',
        }}
      />
      <span>{isID ? 'ID' : 'EN'}</span>
    </button>
  )
}