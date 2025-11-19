import { BrowserRouter, Navigate, Route, Routes } from "react-router";
import AppLayout from "./components/AppLayout";
import SignUp from "./pages/Signup";
import Login from "./pages/Login";
import Home from "./pages/Home";
import Auctions from "./pages/Auctions";
import Auction from "./pages/Auction";

function App() {
  return (
    <>
      <BrowserRouter>
        <Routes>
          <Route element={<AppLayout />}>
            <Route index element={<Navigate replace to="home" />} />
            <Route path="home" element={<Home />} />
            <Route path="auctions" element={<Auctions />} />
            <Route path="auctions/:auctionId" element={<Auction />} />
          </Route>
          <Route path="login" element={<Login />} />
          <Route path="signup" element={<SignUp />} />
        </Routes>
      </BrowserRouter>
    </>
  );
}

export default App;
