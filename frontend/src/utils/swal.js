import Swal from "sweetalert2";

export const confirmAction = (options = {}) => {
  return Swal.fire({
    title: "Yakin?",
    text: "Pesanan akan dikirim ke kasir",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Ya, kirim",
    cancelButtonText: "Batal",
    reverseButtons: true,
    ...options,
  });
};

export const successAlert = (title, text) => {
  return Swal.fire({
    icon: "success",
    title,
    text,
    timer: 2000,
    showConfirmButton: false,
  });
};

export const errorAlert = (text) => {
  return Swal.fire({
    icon: "error",
    title: "Oops!",
    text,
  });
};
