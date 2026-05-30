import { Outlet } from 'react-router-dom'

export default function MainLayout() {
  return (
    <div className="min-h-screen bg-[#FAFAF8]">
      <Outlet />
    </div>
  )
}