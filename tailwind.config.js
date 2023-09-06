/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'src/**/*.{js,ts,jsx,tsx}',
		"templates/**/*.html.twig",
		"assets/**/*.js",
		"assets/**/*.css",
	],
	theme: {
		extend: {
			backgroundImage: {
				'primary': 'linear-gradient(180deg, #B9E6F2 0%, #50B2D4 100%)',
				'savons': 'url("/images/savons_double-feuille--7.png")',
				'bulles-bleues': 'url("/images/bulles--bleues.png")',
				'bulles-irisees': 'url("/images/bulles--irisees.png")',
				'binaire': 'url("/images/fond_binaire.png")',
			},
			colors: {
				'secondary': '#50B2D4',
				'deeper': '#113846',
				'darker': '#257A97',
				'lighter-20': '#89C5DA',
				'lighter-40': '#B9E6F2',
				'headings': '#03035A',
				'error': '#FF4954',
				'warning': '#FFBA00',
				'success': '#8EBF26',
				'information': '#21AA93',
			},
			dropShadow: {
				'darker': '0 0 3.2px rgba(37, 122, 151, 100)',
			},
			boxShadow: {
				'darker': '0 0 0 3.2px rgba(37, 122, 151, 100)',
			},
			maxWidth: {
				'max-content': '1200px',
				'title-mobile-size': '17rem',
			},
			width: {
				'rounders': '61.25rem',
				'rounders--2': '45rem',
			},
			height: {
				'rounders': '31vh',
			},
		},
	},
	plugins: [],
};

