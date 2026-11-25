import { Navigate, Route, Routes } from 'react-router-dom';
import { useAuth } from './auth/AuthContext';
import Layout from './components/Layout';
import ProtectedRoute from './components/ProtectedRoute';
import { homePathFor, ROUTE_ROLES } from './config/permissions';
import LoginPage from './pages/LoginPage';
import EquipmentPage from './pages/EquipmentPage';
import AthletesPage from './pages/AthletesPage';
import AthleteDetailPage from './pages/AthleteDetailPage';
import CoachesPage from './pages/CoachesPage';
import NewLoanPage from './pages/NewLoanPage';
import LoansPage from './pages/LoansPage';
import LoanDetailPage from './pages/LoanDetailPage';
import OverduePage from './pages/OverduePage';
import MostRequestedReportPage from './pages/MostRequestedReportPage';

function guard(roles, element) {
  return <ProtectedRoute roles={roles}>{element}</ProtectedRoute>;
}

export default function App() {
  const { user } = useAuth();

  return (
    <Routes>
      <Route path="/login" element={user ? <Navigate to={homePathFor(user.role)} replace /> : <LoginPage />} />
      <Route element={guard(null, <Layout />)}>
        <Route index element={<Navigate to={user ? homePathFor(user.role) : '/login'} replace />} />
        <Route path="equipment" element={guard(ROUTE_ROLES.equipment, <EquipmentPage />)} />
        <Route path="athletes" element={guard(ROUTE_ROLES.athletes, <AthletesPage />)} />
        <Route path="athletes/:id" element={guard(ROUTE_ROLES.athletes, <AthleteDetailPage />)} />
        <Route path="coaches" element={guard(ROUTE_ROLES.coaches, <CoachesPage />)} />
        <Route path="loans" element={guard(ROUTE_ROLES.loans, <LoansPage />)} />
        <Route path="loans/new" element={guard(ROUTE_ROLES.newLoan, <NewLoanPage />)} />
        <Route path="loans/:id" element={guard(ROUTE_ROLES.loans, <LoanDetailPage />)} />
        <Route path="overdue" element={guard(ROUTE_ROLES.overdue, <OverduePage />)} />
        <Route path="reports/most-requested" element={guard(ROUTE_ROLES.report, <MostRequestedReportPage />)} />
        <Route path="*" element={<section className="card"><h1>Página no encontrada</h1></section>} />
      </Route>
    </Routes>
  );
}
