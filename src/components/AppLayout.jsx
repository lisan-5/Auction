import { Outlet } from "react-router";
import NavBar from "./NavBar";
import Footer from "./Footer";

function AppLayout() {
  return (
    <>
      <NavBar />
      <main>
        <Outlet />
      </main>
      <Footer />
    </>
  );
}

export default AppLayout;
