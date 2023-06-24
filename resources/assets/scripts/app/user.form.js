var app = new Vue({
    el: '#app',
    data: {
        roleUrl: '',
        usersUrl: '',
        selectedRoleId: 0,
        parentRoleId: 0,
        selectedParentId: 0,
        users: []
    },
    computed: {
    },
    methods: {
        setDefaults: function() {
            this.roleUrl = document.querySelector('#role_id').getAttribute('data-url');
            this.usersUrl = document.querySelector('#parent_id').getAttribute('data-url');
            this.selectedRoleId = document.querySelector('#role_id').value;
            this.selectedParentId = document.querySelector('#parent_id').getAttribute('data-default');
        },
        downloadGroupDetails: function() {
            let app = this;
            axios.get(`${app.roleUrl}/${app.selectedRoleId}`).then(function (response) {
                app.parentRoleId = response.data.parent_id;
                app.downloadUsers();
            }).catch(function (error) {
                cc("error", error);
            });
        },
        downloadUsers: function() {
            let app = this;
            axios.get(`${app.usersUrl}?find=${app.parentRoleId}&scope=role_id`).then(function (response) {
                app.users = response.data.map(function (obj) {
                    return { key: obj.id, text: obj.name }
                });
                app.users.unshift({ key: '', text: '(None)'});
                populateOptions('#parent_id', app.users, app.selectedParentId > 0 ? app.selectedParentId : null)
            }).catch(function (error) {
                cc("error", error);
            });
        }
    },
    created: function() {
        this.setDefaults();
    },
    watch: {
        selectedRoleId: function() {
            this.downloadGroupDetails();
        },
    },
});