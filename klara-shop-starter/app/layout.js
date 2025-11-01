export const metadata = { title: "Klara Shop Starter", description: "Minimal test UI for KLARA API" };
export default function RootLayout({ children }) {
  return (
    <html lang="de">
      <body style={{ fontFamily: "ui-sans-serif, system-ui", margin: 0, background: "#0b0b0b", color: "white" }}>
        <div style={{ maxWidth: 1100, margin: "0 auto", padding: "24px" }}>
          <header style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
            <h1 style={{ fontSize: 24, margin: 0 }}>🍷 KLARA Shop Starter</h1>
            <nav style={{ opacity: 0.8 }}><a href="/" style={{ color: "white", textDecoration: "none" }}>Kategorien</a></nav>
          </header>
          {children}
          <footer style={{ marginTop: 40, opacity: 0.6, fontSize: 12 }}><p>Nur Test-UI.</p></footer>
        </div>
      </body>
    </html>
  );
}
