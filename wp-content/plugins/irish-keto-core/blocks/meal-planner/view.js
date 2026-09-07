( function () {
	'use strict';

	// Mirrors Irish_Keto_Core\Bmr_Calculator in class-bmr-calculator.php.
	// Keep both in sync if the formula or the diet/goal/activity tables change.
	const ACTIVITY_MULTIPLIERS = {
		sedentary: 1.2,
		light: 1.375,
		moderate: 1.55,
		active: 1.725,
		very_active: 1.9,
	};

	const GOAL_ADJUSTMENTS = {
		lose: 0.8,
		maintain: 1.0,
		build: 1.1,
	};

	const DIET_MACRO_SPLITS = {
		keto: [ 0.05, 0.2, 0.75 ],
		carnivore: [ 0.01, 0.35, 0.64 ],
		'low-carb': [ 0.2, 0.3, 0.5 ],
		'high-fat-high-protein': [ 0.05, 0.4, 0.55 ],
	};

	function calculate( input ) {
		const age = parseFloat( input.age );
		const height = parseFloat( input.height_cm );
		const weight = parseFloat( input.weight_kg );

		if ( ! age || ! height || ! weight ) {
			return null;
		}

		const activityMultiplier = ACTIVITY_MULTIPLIERS[ input.activity ];
		const goalAdjustment = GOAL_ADJUSTMENTS[ input.goal ];
		const macroSplit = DIET_MACRO_SPLITS[ input.diet ];

		if ( ! activityMultiplier || ! goalAdjustment || ! macroSplit ) {
			return null;
		}

		const sexOffset = input.sex === 'female' ? -161 : 5;
		const bmr = 10 * weight + 6.25 * height - 5 * age + sexOffset;
		const tdee = bmr * activityMultiplier;
		const target = tdee * goalAdjustment;
		const [ carbPct, proteinPct, fatPct ] = macroSplit;

		return {
			bmr: Math.round( bmr ),
			tdee: Math.round( tdee ),
			target_calories: Math.round( target ),
			macros: {
				carbs_g: Math.round( ( target * carbPct ) / 4 ),
				protein_g: Math.round( ( target * proteinPct ) / 4 ),
				fat_g: Math.round( ( target * fatPct ) / 9 ),
			},
		};
	}

	async function fetchMatchingRecipes( dietSlug, dislikesRaw ) {
		try {
			const termRes = await fetch(
				'/wp-json/wp/v2/diet_type?slug=' + encodeURIComponent( dietSlug )
			);
			const terms = await termRes.json();

			if ( ! terms.length ) {
				return [];
			}

			const postsRes = await fetch(
				'/wp-json/wp/v2/recipe?diet_type=' + terms[ 0 ].id + '&per_page=6'
			);
			const posts = await postsRes.json();

			const dislikes = dislikesRaw
				.toLowerCase()
				.split( ',' )
				.map( ( s ) => s.trim() )
				.filter( Boolean );

			return posts.filter( ( post ) => {
				const haystack = ( post.title.rendered + ' ' + post.excerpt.rendered ).toLowerCase();
				return ! dislikes.some( ( d ) => haystack.includes( d ) );
			} );
		} catch ( e ) {
			return null; // Fall back silently; the form's normal GET submit still works.
		}
	}

	function renderResults( container, results, recipes, dietLabel ) {
		const t = ( key, fallback ) => container.dataset[ key ] || fallback;

		let html = '<h3>' + t( 'lblEstimates', 'Your estimated numbers' ) + '</h3>';
		html += '<ul class="irish-keto-meal-planner__stats">';
		html += '<li><strong>' + results.bmr + '</strong> ' + t( 'lblBmr', 'kcal — resting (BMR)' ) + '</li>';
		html += '<li><strong>' + results.tdee + '</strong> ' + t( 'lblTdee', 'kcal — maintenance (TDEE)' ) + '</li>';
		html += '<li><strong>' + results.target_calories + '</strong> ' + t( 'lblTarget', 'kcal — daily target for your goal' ) + '</li>';
		html += '</ul>';
		html += '<h4>' + t( 'lblMacros', 'Suggested daily macros' ) + '</h4>';
		html += '<ul class="irish-keto-meal-planner__macros">';
		html += '<li>' + t( 'lblCarbs', 'Net carbs' ) + ': <strong>' + results.macros.carbs_g + ' g</strong></li>';
		html += '<li>' + t( 'lblProtein', 'Protein' ) + ': <strong>' + results.macros.protein_g + ' g</strong></li>';
		html += '<li>' + t( 'lblFat', 'Fat' ) + ': <strong>' + results.macros.fat_g + ' g</strong></li>';
		html += '</ul>';

		if ( recipes && recipes.length ) {
			html += '<h4>' + t( 'lblRecipes', 'Recipes to get you started' ) + '</h4>';
			html += '<ul class="irish-keto-meal-planner__recipes">';
			recipes.forEach( ( r ) => {
				html += '<li><a href="' + r.link + '">' + r.title.rendered + '</a></li>';
			} );
			html += '</ul>';
		} else if ( recipes && recipes.length === 0 && dietLabel ) {
			html += '<p>' + t( 'lblNoRecipes', "We don't have a matching recipe published yet — check back soon." ) + '</p>';
		}

		html += '<p class="irish-keto-meal-planner__note">' + t(
			'lblNote',
			'These are general estimates for educational purposes, not medical advice, and nothing you enter here is saved or sent anywhere — it stays in your browser.'
		) + '</p>';

		container.innerHTML = html;
		container.hidden = false;
	}

	document.querySelectorAll( '.irish-keto-meal-planner' ).forEach( ( block ) => {
		const form = block.querySelector( '.irish-keto-meal-planner__form' );
		const results = block.querySelector( '[data-irish-keto-planner-results]' );

		if ( ! form || ! results ) {
			return;
		}

		form.addEventListener( 'submit', function ( event ) {
			const consent = form.querySelector( '[name="gp_consent"]' );

			if ( ! consent || ! consent.checked ) {
				return; // Let native "required" validation / server fallback handle it.
			}

			event.preventDefault();

			const data = Object.fromEntries( new FormData( form ).entries() );
			const calculated = calculate( data );

			if ( ! calculated ) {
				results.innerHTML = '<p class="irish-keto-meal-planner__error">Please fill in every field with a realistic value.</p>';
				results.hidden = false;
				return;
			}

			const params = new URLSearchParams( data );
			window.history.pushState( {}, '', '?' + params.toString() );

			fetchMatchingRecipes( data.diet, data.dislikes || '' ).then( ( recipes ) => {
				renderResults( results, calculated, recipes, data.diet );
			} );
		} );
	} );
} )();
