import { useState } from "react";
import api from "./services/api";

export default function App() {
  const [email, setEmail] = useState("test@test.com");
  const [password, setPassword] = useState("test1234");
  const [token, setToken] = useState(localStorage.getItem("token") || "");
  const [ads, setAds] = useState([]);
  const [error, setError] = useState("");

  const login = async () => {
    setError("");
    try {
      const res = await api.post("/api/login_check", { email, password });
      const t = res.data.token;
      localStorage.setItem("token", t);
      setToken(t);
    } catch (e) {
      setError(
        e?.response?.data?.message ||
          "Login failed (vérifie email/mot de passe et le backend)"
      );
    }
  };

  const loadAds = async () => {
    setError("");
    try {
      const res = await api.get("/api/ads");
      setAds(res.data);
    } catch (e) {
      setError(
        e?.response?.data?.message ||
          "Impossible de charger les ads (token manquant/invalid ?)"
      );
    }
  };

  const logout = () => {
    localStorage.removeItem("token");
    setToken("");
    setAds([]);
  };

  return (
    <div style={{ maxWidth: 700, margin: "40px auto", fontFamily: "Arial" }}>
      <h1>Frontend React</h1>

      {!token ? (
        <div style={{ border: "1px solid #ddd", padding: 16, borderRadius: 8 }}>
          <h2>Login</h2>
          <div style={{ display: "grid", gap: 10 }}>
            <input
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="email"
            />
            <input
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="password"
              type="password"
            />
            <button onClick={login}>Se connecter</button>
          </div>
        </div>
      ) : (
        <div style={{ display: "flex", gap: 10, alignItems: "center" }}>
          <span style={{ color: "green" }}> Connecté</span>
          <button onClick={logout}>Logout</button>
        </div>
      )}

      <hr style={{ margin: "24px 0" }} />

      <div style={{ display: "flex", gap: 10 }}>
        <button onClick={loadAds} disabled={!token}>
          Charger les ads
        </button>
        {!token && <small>(connecte-toi d’abord)</small>}
      </div>

      {error && (
        <p style={{ color: "crimson", marginTop: 12 }}>
          <b>Erreur:</b> {error}
        </p>
      )}

      <h2 style={{ marginTop: 24 }}>Ads</h2>
      <pre
        style={{
          background: "#111",
          color: "#0f0",
          padding: 12,
          borderRadius: 8,
          overflow: "auto",
        }}
      >
        {JSON.stringify(ads, null, 2)}
      </pre>
    </div>
  );
}