import { createBrowserRouter } from 'react-router-dom'
import MainLayout from '@/layouts/MainLayout'
import TablePage from '@/pages/TablePage'
import MenuPage from '@/pages/MenuPage'
import CheckoutPage from '@/pages/CheckoutPage'
import OrderStatusPage from '@/pages/OrderStatusPage'
import NotFoundPage from '@/pages/NotFoundPage'
import KitchenPage from '@/pages/KitchenPage'
import PaymentPage from '@/pages/PaymentPage'
import LoyaltyPage from '@/pages/LoyaltyPage'
import ReservationPage from '@/pages/ReservationPage'

const router = createBrowserRouter([
  {
    path: '/table/:slug',
    element: <TablePage />,
  },
  {
    element: <MainLayout />,
    children: [
      {
        path: '/menu',
        element: <MenuPage />,
      },
      {
        path: '/checkout',
        element: <CheckoutPage />,
      },
      {
        path: '/order/:invoice',
        element: <OrderStatusPage />,
      },
      {
        path: '/payment',
        element: <PaymentPage />,
      },
      { path: '/loyalty', 
        element: <LoyaltyPage /> }
    ],
  },
  {
    path: '/kitchen',
    element: <KitchenPage />,
  },
  {
    path: '*',
    element: <NotFoundPage />,
  },
  // tambahkan di dalam children MainLayout
{ path: '/reservation', element: <ReservationPage /> }
])

export default router