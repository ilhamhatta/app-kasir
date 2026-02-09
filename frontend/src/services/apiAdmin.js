import axios from "axios";

const apiAdmin = axios.create({
  baseURL: "http://10.219.3.101:8000/api",
  headers: {
    Accept: "application/json",
  },
});

apiAdmin.interceptors.request.use((config) => {
  const token = localStorage.getItem("admin_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default apiAdmin;
