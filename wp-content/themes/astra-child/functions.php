<?php
// functions.php du thème enfant Astra

function astra_child_enqueue_styles() {
    wp_enqueue_style(
        'astra-parent-style',
        get_template_directory_uri() . '/style.css'
    );

    wp_enqueue_style(
        'astra-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('astra-parent-style')
    );
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');

add_filter('get_custom_logo', function($html) {
    $url = 'https://sciences-techniques.ube.fr/';
    $html = preg_replace('/href="[^"]*"/', 'href="' . esc_url($url) . '"', $html);
    return $html;
});

function afficher_professeurs() {
    $args = array(
        'post_type' => 'professeurs',
        'posts_per_page' => -1
    );

    $query = new WP_Query($args);
    $output = '<div class="professeurs-grid">';

    if($query->have_posts()) {
        while($query->have_posts()) {
            $query->the_post();
            $ue_raw  = get_field('ue');
            $matiere = get_field('matiere');
            $prenom  = get_field('prenom');
            $nom     = get_field('nom');
            $initiale = mb_substr($prenom, 0, 1) . mb_substr($nom, 0, 1);
            $genre   = get_field('genre');
            $statut  = get_field('statut');

            if ($statut === 'intervenant') {
                $titre = ($genre === 'femme') ? 'Intervenante' : 'Intervenant';
            } else {
                $titre = ($genre === 'femme') ? 'Enseignante' : 'Enseignant';
            }

            $ues = array_filter(array_map('trim', explode("\n", trim($ue_raw ?? ''))));

            if (count($ues) > 1) {
                $derniere = array_pop($ues);
                $ue_texte = implode(', ', $ues) . ' et ' . $derniere;
            } else {
                $ue_texte = $ues[0] ?? '';
            }

            $output .= '<div class="professeur-card">';
            $output .= '<div class="professeur-initiale">' . $initiale . '</div>';
            $output .= '<h3>' . $prenom . ' ' . $nom . '</h3>';
            $output .= '<p>' . $titre . ' au sein de l\'' . $ue_texte . '</p>';
            $output .= '<p>' . $matiere . '</p>';
            $output .= '</div>';
        }
    }

    $output .= '</div>';
    wp_reset_postdata();
    return $output;
}
add_shortcode('liste_professeurs', 'afficher_professeurs');

function afficher_ue() {
    $args = array(
        'post_type'      => 'ue',
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);
    $ues = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $numero  = get_field('numero_ue');
            $credits = get_field('credits');
            $num_tri = intval(preg_replace('/[^0-9]/', '', $numero));

            $ues[] = array(
                'numero'    => $numero,
                'credits'   => $credits,
                'titre'     => get_the_title(),
                'permalink' => get_permalink(),
                'num_tri'   => $num_tri
            );
        }
        wp_reset_postdata();
    }

    usort($ues, function($a, $b) {
        return $a['num_tri'] - $b['num_tri'];
    });

    $output = '<div class="ue-grid">';
    foreach ($ues as $ue) {
        $output .= '<div class="ue-card">';
        $output .= '<div class="ue-card-header">';
        $output .= '<span class="ue-numero">' . esc_html($ue['numero']) . '</span>';
        $output .= '<span class="ue-credits">' . esc_html($ue['credits']) . ' crédits</span>';
        $output .= '</div>';
        $titre_decode = html_entity_decode($ue['titre'], ENT_QUOTES, 'UTF-8');
        $titre_court = preg_replace('/^UE\d+\s*[-–—]\s*/u', '', $titre_decode);
        $output .= '<h3>' . esc_html($titre_court) . '</h3>';
        $output .= '<a class="ue-btn" href="' . esc_url($ue['permalink']) . '">Détails du module</a>';
        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('liste_ue', 'afficher_ue');