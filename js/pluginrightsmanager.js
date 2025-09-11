$(document).ready(function() {
    
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
        if (confirm('Êtes-vous sûr de vouloir supprimer ce droit ?')) {
            $.ajax({
                url: '/plugins/pluginrightsmanager/ajax/delete_right.php',
                type: 'POST',
                data: {
                    id: rightId,
                    _glpi_csrf_token: $('input[name="_glpi_csrf_token"]').val()
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Erreur lors de la suppression');
                }
            });
        }
    };
    
    // Toggle des droits personnalisés
    $('.toggle-custom-rights').click(function() {
        var pluginName = $(this).data('plugin');
        $('#custom-rights-' + pluginName).toggle();
    });
});