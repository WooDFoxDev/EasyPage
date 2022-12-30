const uiSlide = {
  speed: "0.15s",
  easing: "ease-in",
  initTarget(el, speed, easing) {
    if (el.getAttribute("data-uislide-height") == null) {
      const temp = document.createElement("div");
      temp.style.height = "0px";
      el.parentNode.append(temp);

      const clone = el.cloneNode(true);
      clone.classList.remove("d-none");
      clone.style.height = "auto";
      clone.style.display = "block";
      temp.append(clone);

      let height = `${clone.offsetHeight}px`;
      el.setAttribute("data-uislide-height", height);
      el.style.overflow = "hidden";

      if (el.style.display == "none" || el.classList.contains("d-none")) {
        el.style.transition = "";
        el.style.height = "0px";
        el.style.display = "block";
        el.classList.remove("d-none");
        el.setAttribute("data-uislide-state", "up");
      } else {
        el.setAttribute("data-uislide-state", "down");
      }

      temp.remove();
    }

    el.style.transition = `height ${speed} ${easing}`;
  },
  slideUp(el, speed = uiSlide.speed, easing = uiSlide.easing) {
    uiSlide.initTarget(el, speed, easing);

    el.style.height = "0px";
    el.setAttribute("data-uislide-state", "up");
  },
  slideDown(el, speed = uiSlide.speed, easing = uiSlide.easing) {
    uiSlide.initTarget(el, speed, easing);

    el.style.height = el.getAttribute("data-uislide-height");
    el.setAttribute("data-uislide-state", "down");
  },
  slideToggle(el, speed = uiSlide.speed, easing = uiSlide.easing) {
    uiSlide.initTarget(el, speed, easing);

    if (el.getAttribute("data-uislide-state") == "down") {
      uiSlide.slideUp(el, speed, easing);
    } else {
      uiSlide.slideDown(el, speed, easing);
    }
  },
  init(selector, speed = uiSlide.speed, easing = uiSlide.easing) {
    document.querySelectorAll(selector).forEach((el) => {
      document
        .querySelectorAll(el.getAttribute("data-target"))
        .forEach((target) => {
          uiSlide.initTarget(target, speed, easing);
        });

      el.addEventListener("click", (event) => {
        event.preventDefault();

        document
          .querySelectorAll(el.getAttribute("data-target"))
          .forEach((target) => {
            uiSlide.slideToggle(target, speed, easing);
          });
      });
    });
  },
};

export default uiSlide;
