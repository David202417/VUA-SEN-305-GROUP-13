(() => {
  "use strict";

  const form = document.getElementById("registration-form");
  if (!form) return;

  const currentYear = new Date().getFullYear();
  const admissionYear = document.getElementById("admissionYear");
  const dateOfBirth = document.getElementById("dob");
  admissionYear.max = String(currentYear + 1);

  const oldestAllowedBirthday = new Date();
  oldestAllowedBirthday.setFullYear(oldestAllowedBirthday.getFullYear() - 15);
  dateOfBirth.max = oldestAllowedBirthday.toISOString().slice(0, 10);

  const phonePattern = /^\+?[0-9 ()-]{7,20}$/;
  const namePattern = /^[\p{L}\s'-]{2,}$/u;

  const rules = {
    studentID: {
      test: (value) => /^VUG\/SEN\/\d{2}\/\d{5}$/.test(value.trim().toUpperCase()),
      message: "Use the format VUG/SEN/25/12345.",
    },
    email: {
      test: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value.trim()),
      message: "Enter a valid email address.",
    },
    password: {
      test: (value) => value.length >= 8 && value.length <= 72 && /[A-Za-z]/.test(value) && /\d/.test(value),
      message: "Use 8–72 characters with at least one letter and one number.",
    },
    confirmPassword: {
      test: (value) => value !== "" && value === document.getElementById("password").value,
      message: "Passwords must match.",
    },
    firstName: { test: (value) => namePattern.test(value.trim()), message: "Enter at least two letters." },
    lastName: { test: (value) => namePattern.test(value.trim()), message: "Enter at least two letters." },
    middleName: {
      test: (value) => value.trim() === "" || namePattern.test(value.trim()),
      message: "Enter at least two letters or leave this blank.",
    },
    dob: {
      test: (value) => {
        if (!value) return false;
        const date = new Date(`${value}T00:00:00`);
        return !Number.isNaN(date.getTime()) && date <= oldestAllowedBirthday;
      },
      message: "The student must be at least 15 years old.",
    },
    gender: { test: (value) => value !== "", message: "Select a gender option." },
    phone: { test: (value) => phonePattern.test(value.trim()), message: "Enter a valid phone number." },
    address: { test: (value) => value.trim().length >= 10, message: "Enter a complete home address." },
    state: { test: (value) => namePattern.test(value.trim()), message: "Enter a valid state name." },
    nationality: { test: (value) => namePattern.test(value.trim()), message: "Enter a valid nationality." },
    department: { test: (value) => value.trim().length >= 2, message: "Enter your department." },
    faculty: { test: (value) => value.trim().length >= 2, message: "Enter your faculty." },
    course: { test: (value) => value.trim().length >= 2, message: "Enter your course of study." },
    level: { test: (value) => value !== "", message: "Select your current level." },
    admissionYear: {
      test: (value) => Number.isInteger(Number(value)) && Number(value) >= 1990 && Number(value) <= currentYear + 1,
      message: `Enter a year from 1990 to ${currentYear + 1}.`,
    },
    registrationDate: {
      test: (value) => {
        const year = Number(admissionYear.value);
        return Boolean(value) && Number.isInteger(year) && value >= `${year}-01-01`;
      },
      message: "Registration must be on or after the admission year.",
    },
    guardianName: { test: (value) => namePattern.test(value.trim()), message: "Enter the guardian's full name." },
    guardianPhone: { test: (value) => phonePattern.test(value.trim()), message: "Enter a valid phone number." },
    emergencyContact: { test: (value) => phonePattern.test(value.trim()), message: "Enter a valid emergency number." },
  };

  function errorElement(field) {
    const container = field.closest(".field");
    let error = container.querySelector(".field-error");
    if (!error) {
      error = document.createElement("span");
      error.className = "field-error";
      error.id = `${field.id}-error`;
      container.appendChild(error);
      field.setAttribute("aria-describedby", error.id);
    }
    return error;
  }

  function validateField(id) {
    const field = document.getElementById(id);
    if (!field) return true;

    const valid = rules[id].test(field.value);
    field.classList.toggle("valid", valid);
    field.classList.toggle("invalid", !valid);
    field.setAttribute("aria-invalid", String(!valid));
    errorElement(field).textContent = valid ? "" : rules[id].message;
    return valid;
  }

  Object.keys(rules).forEach((id) => {
    const field = document.getElementById(id);
    if (!field) return;

    errorElement(field);
    field.addEventListener("blur", () => validateField(id));
    field.addEventListener("input", () => {
      if (field.classList.contains("valid") || field.classList.contains("invalid")) {
        validateField(id);
      }
      if (id === "password" && document.getElementById("confirmPassword").value) {
        validateField("confirmPassword");
      }
    });
    field.addEventListener("change", () => validateField(id));
  });

  const params = new URLSearchParams(window.location.search);
  const pageMessage = document.getElementById("page-message");
  const errors = {
    invalid: "Some information was invalid. Please review the highlighted fields and try again.",
    password: "Use a password with 8–72 characters, including a letter and a number, and make sure both passwords match.",
    exists: "An account already exists for that email address or student ID.",
    server: "The account could not be created right now. Please try again.",
  };
  if (params.has("error")) {
    pageMessage.textContent = errors[params.get("error")] || errors.invalid;
    pageMessage.className = "notice notice--error";
    pageMessage.hidden = false;
  }

  form.addEventListener("submit", (event) => {
    const results = Object.keys(rules).map((id) => ({ id, valid: validateField(id) }));
    const firstInvalid = results.find((result) => !result.valid);

    if (firstInvalid) {
      event.preventDefault();
      const field = document.getElementById(firstInvalid.id);
      field.focus();
      field.scrollIntoView({ behavior: "smooth", block: "center" });
      pageMessage.textContent = "Please correct the highlighted fields before submitting.";
      pageMessage.className = "notice notice--error";
      pageMessage.hidden = false;
      return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = "Creating account…";
  });

  form.addEventListener("reset", () => {
    window.setTimeout(() => {
      form.querySelectorAll(".valid, .invalid").forEach((field) => {
        field.classList.remove("valid", "invalid");
        field.removeAttribute("aria-invalid");
      });
      form.querySelectorAll(".field-error").forEach((error) => {
        error.textContent = "";
      });
      pageMessage.hidden = true;
    }, 0);
  });
})();

