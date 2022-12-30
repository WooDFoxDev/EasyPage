import uiLocker from "./modules/uiLocker.js";
import uiToast from "./modules/uiToast.js";
import uiSlide from "./modules/uiSlide.js";

const _uiLocker = new uiLocker();
const _uiToast = new uiToast();

const ajaxRequest = async (url, data, method = "POST", form) => {
  const availableMethods = ["GET", "POST", "PUT", "PATCH", "DELETE"];

  method = method.toUpperCase();
  if (!availableMethods.includes(method)) {
    throw new Error("Method not allowed");
  }

  let csrf_token = null;
  const csrf = document.querySelector('meta[name="csrf-token"]');
  if (csrf != null) {
    csrf_token = csrf.getAttribute("content");
  }

  const request = {
    method: method,
    mode: "same-origin",
    cache: "no-cache",
    credentials: "same-origin",
    headers: {
      Accept: "application/json",
      "X-CSRF-TOKEN": csrf_token ?? undefined,
    },
    redirect: "follow", // manual, *follow, error
    referrerPolicy: "no-referrer",
    body: data,
  };

  const response = await fetch(url, request);

  // await new Promise((r) => setTimeout(r, 150));

  if (response.ok) {
    await handleResponse(response.json(), form);
  } else {
    _uiToast.addErrorToast("Request can't be completed, please check server settings");
  }
};

const ajaxBlockingRequest = async (url, data, method = "POST", form) => {
  _uiLocker.block();

  await ajaxRequest(url, data, method, form);

  _uiLocker.release();
};

const handleResponse = async (data, form) => {
  data
    .then((data) => {
      console.log(data);

      if (data._token_refresh !== undefined) {
        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf != null) {
          csrf.setAttribute("content", data._token_refresh);
        }

        document.querySelectorAll('input[name="_token"]').forEach((el) => {
          el.value = data._token_refresh;
        });
      }

      if (data.status == undefined || data.status != "ok") {
        if (data.message !== undefined) {
          _uiToast.addErrorToast(data.message);
        }

        if (form !== undefined) {
          if (data.validator != undefined && typeof data.validator == "object") {
            for (const field in data.validator) {
              let input = form.querySelector(".validation-" + field + " .form-control");

              if (input !== null) {
                if (data.validator[field].length == 0) {
                  input.classList.add("is-valid");
                  input.classList.remove("is-invalid");
                } else {
                  input.classList.add("is-invalid");
                  input.classList.remove("is-valid");

                  let feedback = form.querySelector(".validation-" + field + " .invalid-feedback");

                  if (feedback !== null) {
                    feedback.innerText = "";

                    data.validator[field].forEach((message) => {
                      feedback.append(message);
                    });
                  }
                }
              }
            }
          }
        }
      } else if (data.status == "ok") {
        if (data.message !== undefined) {
          _uiToast.addSuccessToast(data.message);
        }
      }

      if (data.next_page !== undefined) {
        window.location = data.next_page;
      }

      if (data.download !== undefined) {
        const link = document.createElement("a");
        link.setAttribute("href", data.download);
        link.click();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
};

document.querySelectorAll("form.ajax").forEach((form) => {
  form.addEventListener("submit", (event) => {
    event.preventDefault();

    let url = form.getAttribute("action") ?? window.location;
    let method = form.getAttribute("method") ?? "post";

    const data = new FormData(form);

    if (!form.classList.contains("block-ui")) {
      ajaxRequest(url, data, method, form);
    } else {
      ajaxBlockingRequest(url, data, method, form);
    }
  });
});

document.querySelectorAll("a.ajax").forEach((a) => {
  a.addEventListener("click", (event) => {
    event.preventDefault();

    let url = a.getAttribute("href") ?? window.location;
    let method = a.getAttribute("data-method") ?? "post";

    const data = new FormData();
    // TODO: Append formData with data attributes
    // data.append(field.getAttribute("name"), field.value);

    if (!a.classList.contains("block-ui")) {
      ajaxRequest(url, data, method);
    } else {
      ajaxBlockingRequest(url, data, method);
    }
  });
});

uiSlide.init(".toggle");
