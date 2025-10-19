// tailwind.config.js
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    // This line scans all files in your root directory.
    "./*.{html,js,php}", 
    // This line scans all files in the 'assets' folder and its subfolders.
    "./assets/**/*.{html,js,php}",
    // This line scans all files in the 'controllers' folder and its subfolders.
    "./controllers/**/*.{html,js,php}", 
    // This line scans all files in the 'includes' folder and its subfolders.
    "./includes/**/*.{html,js,php}", 
    // This line scans all files in the 'pages' folder and its subfolders.
    "./pages/**/*.{html,js,php}",
  ],
  darkMode: 'class',
  theme: {
    extend: {},
  },
  plugins: [],
}