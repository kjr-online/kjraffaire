<?php
// Chargement de l'environnement Dolibarr
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

header('Content-Type: application/json');

// Vérifier les permissions
if (!$user->rights->societe->lire) {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Récupération du `htmlname` envoyé en AJAX
$htmlname = GETPOST('htmlname', 'alpha');

// Création de l'objet formulaire
$form = new Form($db);

// Génération du select mis à jour
$html_select = $form->select_contact(
    '',
    '',
    $htmlname,
    1,
    '',
    '',
    1,
    'style="width: 120px; font-size: 12px;"',
    0,
    1,
    '',
    '',
    'style="width: 120px; font-size: 12px;"'
);

// Ajout de l'option "Ajouter nouveau" si elle n'existe pas déjà
$option_create_new = '<option value="create_new" data-url="' . DOL_URL_ROOT . '/contact/card.php?leftmenu=contacts&action=create">Créer nouveau contact</option>';

if (!str_contains($html_select, 'value="create_new"')) {
    $html_select = str_replace('</select>', $option_create_new . '</select>', $html_select);
}

// Ajout du script pour la gestion de l'ajout
$html_script = '<script>
    $(document).ready(function () {
        let selectField = $("#' . $htmlname . '");

        // Restaurer la valeur précédente après mise à jour
        let previousValue = selectField.data("previous-value") || selectField.val();

        selectField.select2({
            language: {
                noResults: function () {
                    return $("<li class=\'select2-results__option create-option\' data-url=\'" + $("option[value=\'create_new\']").data("url") + "\' style=\'font-weight:bold; color:red; cursor:pointer;\'>Créer nouveau contact</li>");
                }
            },
            escapeMarkup: function (markup) {
                return markup;
            }
        });

        if (previousValue) {
            selectField.val(previousValue).trigger("change");
        }

        // Gérer le clic sur "Ajouter nouveau"
        $(document).off("click", ".create-option").on("click", ".create-option", function () {
            let createUrl = $(this).data("url");

            let newWindow = window.open(createUrl, "_blank");

            let checkWindow = setInterval(function () {
                if (newWindow.closed) {
                    clearInterval(checkWindow);

                    $.ajax({
                        url: "' . DOL_URL_ROOT . '/custom/kjraffaire/ajax/get_contact_select.php",
                        data: { htmlname: "' . $htmlname . '" },
                        dataType: "json",
                        success: function (response) {
                            selectField.select2("destroy"); 
                            selectField.replaceWith(response.html);
                            $("#" + "' . $htmlname . '").data("previous-value", previousValue).select2();
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log("Erreur AJAX:", textStatus, errorThrown);
                        }
                    });
                }
            }, 1000);
        });
    });
</script>';

// Retourner le HTML du select et le script
echo json_encode(['html' => $html_select . $html_script]);
?>
