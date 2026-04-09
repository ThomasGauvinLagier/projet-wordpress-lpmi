<?php
/**
 * Template pour l'affichage d'une UE
 * À placer dans wp-content/themes/astra-child/
 */

get_header();

while (have_posts()) :
    the_post();

    $numero_ue   = get_field('numero_ue');
    $credits     = get_field('credits');
    $heures_cm   = get_field('heures_cm');
    $heures_td   = get_field('heures_td');
    $heures_tp   = get_field('heures_tp');

    $prerequis     = array_filter(explode("\n", trim(get_field('prerequis') ?? '')));
    $competences   = array_filter(explode("\n", trim(get_field('competences') ?? '')));
    $bibliographie = array_filter(explode("\n", trim(get_field('bibliographie') ?? '')));

    $eval_principales_raw = array_filter(explode("\n", trim(get_field('evaluations_principales') ?? '')));
    $eval_rattrapage_raw  = array_filter(explode("\n", trim(get_field('evaluations_rattrapage') ?? '')));

    $remarques = get_field('remarques');

    function parse_evals($lines) {
        $result = [];
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            $result[] = [
                'type'        => trim($parts[0] ?? ''),
                'nature'      => trim($parts[1] ?? ''),
                'coefficient' => trim($parts[2] ?? ''),
            ];
        }
        return $result;
    }

    $evals_principales = parse_evals($eval_principales_raw);
    $evals_rattrapage  = parse_evals($eval_rattrapage_raw);

    // Récupère les profs associés à cette UE et les regroupe par statut
    $profs_query = new WP_Query(array(
        'post_type'      => 'professeurs',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'ue',
                'value'   => $numero_ue,
                'compare' => 'LIKE'
            )
        )
    ));

    $profs_par_statut = ['professeur' => [], 'intervenant' => []];
    if ($profs_query->have_posts()) :
        while ($profs_query->have_posts()) : $profs_query->the_post();
            $prenom = get_field('prenom');
            $nom    = get_field('nom');
            $statut = get_field('statut') ?? 'professeur';
            $profs_par_statut[$statut][] = $prenom . ' ' . $nom;
        endwhile;
        wp_reset_postdata();
    endif;
?>

<div class="ue-single-page">

    <!-- HEADER -->
    <div class="ue-single-header">
        <div class="ue-single-header-left">
            <span class="ue-mention">Sciences, Technologies, Santé (STS)</span>
            <h1><?php echo esc_html(mb_strtoupper(html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8'), 'UTF-8')); ?></h1>
            <?php if (!empty($profs_par_statut['professeur'])) : ?>
            <p class="ue-header-profs">Professeurs : <?php echo implode(', ', array_map('esc_html', $profs_par_statut['professeur'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($profs_par_statut['intervenant'])) : ?>
            <p class="ue-header-profs">Intervenants : <?php echo implode(', ', array_map('esc_html', $profs_par_statut['intervenant'])); ?></p>
            <?php endif; ?>
        </div>
        <div class="ue-single-header-right">
            <div class="ue-header-info-card">
                <span class="ue-header-info-label">Composante</span>
                <span class="ue-header-info-value">UFR Sciences et Techniques</span>
            </div>
            <div class="ue-header-info-card">
                <span class="ue-header-info-label">ECTS</span>
                <span class="ue-header-info-value"><?php echo esc_html($credits); ?> Crédits</span>
            </div>
        </div>
    </div>

    <!-- CONTENU -->
    <div class="ue-single-body">
        <div class="ue-single-main">

            <!-- HEURES -->
            <?php if ($heures_cm || $heures_td || $heures_tp) : ?>
            <section class="ue-section">
                <h2 class="ue-section-title">Heures d'enseignements</h2>
                <div class="ue-heures-grid">
                    <?php if ($heures_cm) : ?>
                    <div class="ue-heure-card">
                        <span class="ue-heure-nb"><?php echo esc_html($heures_cm); ?>H</span>
                        <span class="ue-heure-label">Cours Magistral</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($heures_td) : ?>
                    <div class="ue-heure-card">
                        <span class="ue-heure-nb"><?php echo esc_html($heures_td); ?>H</span>
                        <span class="ue-heure-label">Travaux Dirigés</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($heures_tp) : ?>
                    <div class="ue-heure-card">
                        <span class="ue-heure-nb"><?php echo esc_html($heures_tp); ?>H</span>
                        <span class="ue-heure-label">Travaux Pratiques</span>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- EVALUATIONS -->
            <?php if (!empty($evals_principales) || !empty($evals_rattrapage)) : ?>
            <section class="ue-section">
                <h2 class="ue-section-title">Modalités de contrôle des connaissances</h2>
                <table class="ue-eval-table">
                    <?php if (!empty($evals_principales)) : ?>
                    <tr class="ue-eval-header-row">
                        <td colspan="3">Évaluation initiale / Session principale</td>
                    </tr>
                    <tr class="ue-eval-cols">
                        <th>Type d'évaluation</th>
                        <th>Nature de l'évaluation</th>
                        <th>Coefficient</th>
                    </tr>
                    <?php foreach ($evals_principales as $eval) : ?>
                    <tr>
                        <td><?php echo esc_html($eval['type']); ?></td>
                        <td><?php echo esc_html($eval['nature']); ?></td>
                        <td><?php echo esc_html($eval['coefficient']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($evals_rattrapage)) : ?>
                    <tr class="ue-eval-header-row">
                        <td colspan="3">Seconde chance / Session de rattrapage</td>
                    </tr>
                    <tr class="ue-eval-cols">
                        <th>Type d'évaluation</th>
                        <th>Nature de l'évaluation</th>
                        <th>Coefficient</th>
                    </tr>
                    <?php foreach ($evals_rattrapage as $eval) : ?>
                    <tr>
                        <td><?php echo esc_html($eval['type']); ?></td>
                        <td><?php echo esc_html($eval['nature']); ?></td>
                        <td><?php echo esc_html($eval['coefficient']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($remarques) : ?>
                    <tr>
                        <td colspan="3" class="ue-remarques">
                            Remarques :<br><?php echo nl2br(esc_html($remarques)); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </section>
            <?php endif; ?>

            <!-- COMPÉTENCES -->
            <?php if (!empty($competences)) : ?>
            <section class="ue-section">
                <h2 class="ue-section-title">Compétences Ciblées</h2>
                <div class="ue-competences-grid">
                    <?php foreach ($competences as $comp) : ?>
                    <span class="ue-competence-tag"><?php echo esc_html(trim($comp)); ?></span>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

        </div>

        <!-- SIDEBAR -->
        <aside class="ue-single-sidebar">

            <?php if (!empty($prerequis)) : ?>
            <div class="ue-sidebar-card">
                <h3>Prérequis</h3>
                <ul class="ue-sidebar-list">
                    <?php foreach ($prerequis as $item) : ?>
                    <li><?php echo esc_html(trim($item)); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($bibliographie)) : ?>
            <div class="ue-sidebar-card">
                <h3>Bibliographie</h3>
                <ul class="ue-biblio-list">
                    <?php foreach ($bibliographie as $item) : ?>
                    <li><?php echo esc_html(trim($item)); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

        </aside>
    </div>

</div>

<?php
endwhile;
get_footer();
?>