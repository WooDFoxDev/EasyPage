class uiLocker {
  constructor() {
    const overlay = document.createElement("div");
    overlay.classList.add("overlay");
    overlay.innerHTML = `
    <div class="container h-100">
      <div class="row h-100 align-items-center justify-content-center">
        <div class="spinner-border text-accent" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>
    `;

    this.element = overlay;
    this.appearing = false;
    this.opacity = 0;
    this.timer;

    document.querySelector("body").append(overlay);
  }
  block() {
    this.fadeIn();
  }
  release() {
    this.fadeOut();
  }
  fadeIn() {
    this.appearing = true;

    this.fade();
  }
  fadeOut() {
    this.appearing = false;

    this.fade();
  }
  fade() {
    if (this.appearing) {
      if (this.opacity <= 0) {
        this.element.style.display = "block";
      } else if (this.opacity >= 1) {
        clearInterval(this.timer);
        this.timer = undefined;
        return;
      }

      this.opacity += 0.05;
    } else {
      if (this.opacity <= 0) {
        this.element.style.display = "none";
        clearInterval(this.timer);
        this.timer = undefined;
        return;
      }

      this.opacity -= 0.05;
    }

    if (this.opacity > 1) {
      this.opacity = 1;
    }
    if (this.opacity < 0) {
      this.opacity = 0;
    }

    this.element.style.opacity = this.opacity;

    if (this.timer === undefined) {
      var _uilocker_handle = this;
      this.timer = setInterval(function () {
        _uilocker_handle.fade();
      }, 10);
    }
  }
}

export default uiLocker;
