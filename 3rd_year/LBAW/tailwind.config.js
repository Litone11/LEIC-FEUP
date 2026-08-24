/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        atlas: {
          50: "#f6f7fb",
          500: "#724e90ff",
          900: "#0c1120",
        },
      },
      fontFamily: {
        sans: ['"Instrument Sans"', "ui-sans-serif", "system-ui"],
      },
    },
  },
  plugins: [],
};