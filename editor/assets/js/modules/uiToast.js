class uiToast {
  constructor(position_x = "center", position_y = "bottom") {
    this.position_x = position_x;
    this.position_y = position_y;

    this.init();
  }
  init() {
    const toastContainer = document.createElement("div");
    toastContainer.setAttribute("id", "toastContainer");
    toastContainer.classList.add("toast-container", "p-3", "fixed-bottom");

    if (this.position_x == "center") {
      toastContainer.classList.add("start-50", "translate-middle-x");
    }
    if (this.position_y == "bottom") {
      toastContainer.classList.add("bottom-0");
    }

    document.querySelector("body").append(toastContainer);

    this.container = toastContainer;
  }
  placeToast(toast) {
    this.container.append(toast);
    toast.style.opacity = 1;
  }
  addToast(message) {
    const toast = this.initToast();

    toast.innerHTML = `
        <div class="toast-header">
            <svg class="bd-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveAspectRatio="xMidYMid slice" focusable="false"><rect width="100%" height="100%" fill="#007aff"></rect></svg>
            <strong class="me-auto">Bootstrap</strong>
            <small class="text-muted">just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;

    this.placeToast(toast);
  }
  addMiniToast(message, status = "success") {
    const toast = this.initToast();
    toast.classList.add("align-items-center", "text-bg-" + status, "border-0");

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    setTimeout(() => {
      toast.remove();
    }, 2000);

    this.placeToast(toast);
  }
  addErrorToast(message) {
    this.addMiniToast(message, "danger");
  }
  addSuccessToast(message) {
    this.addMiniToast(message, "success");
  }
  initToast() {
    const toast = document.createElement("div");
    toast.classList.add("toast", "show");
    toast.setAttribute("role", "alert");
    toast.setAttribute("aria-live", "assertive");
    toast.setAttribute("aria-atomic", "true");
    toast.style.opacity = 0;
    toast.style.transition = "opacity .3s";

    return toast;
  }
}

export default uiToast;
