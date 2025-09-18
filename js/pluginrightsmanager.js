    // Log JS lors de la soumission du formulaire d'attribution de droits
    $('form.add-right-form').submit(function(e) {
        const data = $(this).serializeArray();
        console.log('[DEBUG JS] Données envoyées:', data);
    });
$(document).ready(function() {

    // ✅ Vérification avant soumission du formulaire d'ajout de droits standards uniquement
    $('form.add-right-form').submit(function(e) {
        const assignType = $(this).find('select[name="assign_type"]').val();
        const hasSelection =
            (assignType === 'user' && $(this).find('select[name="user_id"]').val()) ||
            (assignType === 'group' && $(this).find('select[name="group_id"]').val()) ||
            (assignType === 'profile' && $(this).find('select[name="profile_id"]').val());

        if (!hasSelection) {
            e.preventDefault();
            Swal.fire('Erreur', 'Veuillez sélectionner un utilisateur, un groupe ou un profil.', 'error');
        }
    });

    // Gestion du sélecteur dynamique d'assignation
    $('select[name="assign_type"]').change(function() {
        var type = $(this).val();
        var selector = $('#assign_selector');
        var pluginRoot = '/plugins/pluginrightsmanager';

        if (type === 'user') {
            $.ajax({
                url: pluginRoot + '/ajax/users.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var html = '<select name="user_id" required>';
                    html += '<option value="">-- Sélectionner un utilisateur --</option>';
                    $.each(data, function(index, user) {
                        html += '<option value="' + user.id + '">' + user.text + '</option>';
                    });
                    html += '</select>';
                    selector.html(html);
                },
                error: function() {
                    selector.html('Erreur lors du chargement des utilisateurs');
                }
            });
        } else if (type === 'group') {
            $.ajax({
                url: pluginRoot + '/ajax/groups.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var html = '<select name="group_id" required>';
                    html += '<option value="">-- Sélectionner un groupe --</option>';
                    $.each(data, function(index, group) {
                        html += '<option value="' + group.id + '">' + group.text + '</option>';
                    });
                    html += '</select>';
                    selector.html(html);
                },
                error: function() {
                    selector.html('Erreur lors du chargement des groupes');
                }
            });
        } else if (type === 'profile') {
            $.ajax({
                url: pluginRoot + '/ajax/profiles.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var html = '<select name="profile_id" required>';
                    html += '<option value="">-- Sélectionner un profil --</option>';
                    $.each(data, function(index, profile) {
                        html += '<option value="' + profile.id + '">' + profile.text + '</option>';
                    });
                    html += '</select>';
                    selector.html(html);
                },
                error: function() {
                    selector.html('Erreur lors du chargement des profils');
                }
            });
        } else {
            selector.html('Sélectionnez d\'abord un type');
        }
    });

    // Confirmation de suppression
    window.deleteRight = function(rightId) {
        Swal.fire({
            title: '🗑️ Supprimer ce droit ?',
            html: '<p>Cette action est <strong>irréversible</strong>.<br>Souhaitez-vous vraiment continuer ?</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            customClass: {
                popup: 'swal2-custom-popup',
                confirmButton: 'swal2-confirm-button',
                cancelButton: 'swal2-cancel-button'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/plugins/pluginrightsmanager/ajax/delete_right.php',
                    type: 'POST',
                    data: {
                        id: rightId,
                        _glpi_csrf_token: $('input[name="_glpi_csrf_token"]').val()
                    },
                    success: function(response) {
                        Swal.fire({
                            title: '✅ Supprimé !',
                            text: 'Le droit a été supprimé avec succès.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('❌ Erreur', 'La suppression a échoué.', 'error');
                    }
                });
            }
        });
    };

    // Toggle des droits personnalisés
    $('.toggle-custom-rights').click(function() {
        var pluginName = $(this).data('plugin');
        $('#custom-rights-' + pluginName).toggle();
    });
});