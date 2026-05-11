/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: "#4B49AC",
          foreground: "#FFFFFF",
        },
        secondary: {
          DEFAULT: "#A3A4A5",
          foreground: "#FFFFFF",
        },
        success: {
          DEFAULT: "#57B657",
          foreground: "#FFFFFF",
        },
        info: {
          DEFAULT: "#248AFD",
          foreground: "#FFFFFF",
        },
        warning: {
          DEFAULT: "#FFC100",
          foreground: "#000000",
        },
        danger: {
          DEFAULT: "#FF4747",
          foreground: "#FFFFFF",
        },
      },
      fontFamily: {
        nunito: ["Nunito", "sans-serif"],
      },
      borderRadius: {
        lg: "8px",
      }
    },
  },
  plugins: [],
}
