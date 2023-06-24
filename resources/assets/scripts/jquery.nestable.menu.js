let editor = {
    data: [],
    maxDepth: 2,
    canUpdate: true,
    canDelete: true,
    container: $('#container'),
    newItemButton: $('#new-item'),
    formModal: $('#modal'),
    formModalForm: $('#modal').find('form'),
    formModalButton: $('#modal').find('button'),
    createCallback: null,
    updateCallback: null,
    deleteCallback: null,
    orderCallback: null,
    setData: function (data) {
        if(isString(data)) data = JSON.parse(data);
        this.data = data;
    },
    setContainer: function (selector) {
        this.container = $(selector);
    },
    setNewItemButton: function (selector) {
        this.newItemButton = $(selector);
    },
    setFormModal: function (modalselector, formSelector, saveButtonSelector) {
        this.formModal = $(modalselector);
        this.formModalForm = $(formSelector);
        this.formModalButton = $(saveButtonSelector);
    },
    onCreate: function (callback) {
        this.createCallback = callback;
    },
    onUpdate: function (callback) {
        this.updateCallback = callback;
    },
    onDelete: function (callback) {
        this.deleteCallback = callback;
    },
    onReorder: function (callback) {
        this.orderCallback = callback;
    },
    showCreateModal: function () {
        var self = this;
        self.formModal.modal('show');
        self.formModalForm.find(`input`).val('');
        self.formModalForm.find(`select`).prop('selectedIndex', 0);;
        self.formModalButton.off('click');
        self.formModalButton.click(function () {
            let formArray = self.formModalForm.serializeArray();
            self.createCallback(self.getEditedObject());
            self.setNew(formArray);
            self.formModal.modal('hide');
        })
    },
    showUpdateModal: function (id) {
        var self = this;
        let item = self.getById(id);
        for(prop in item) {
            if (Object.prototype.hasOwnProperty.call(item, prop)) {
                self.formModalForm.find(`[name=${prop}]`).val(item[prop]);
            }
        }
        self.formModal.modal('show');
        self.formModalButton.off('click');
        self.formModalButton.click(function () {
            let formArray = self.formModalForm.serializeArray();
            self.updateCallback(self.getEditedObject());
            self.setById(id, formArray);
            self.formModal.modal('hide');
        })
    },
    delete: function (id) {
        var self = this;
        let item = self.getById(id);
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to restore the item once deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                self.deleteCallback({ id: id });
            }
        })
    },
    getById: function (id, struct = false) {
        struct = struct ? struct : this.data;
        for(i in struct) {
            let item = struct[i];
            if(item.id == id) return item;
            else {
                let child = false;
                if(item.children) child = this.getById(id, item.children)
                if(child) return child;
            }
        }
    },
    setById: function (id, formData) {
        item = this.getById(id, this.data);
        for(i in formData) {
            let data = formData[i];
            item[data.name] = data.value;
        }
        this.redraw();
    },
    setNew: function (formData) {
        let item = {};
        for(i in formData) {
            let data = formData[i];
            item[data.name] = data.value;
        }
        this.data.unshift(item);
        this.redraw();
    },
    getParentId: function (element) {
        var parent = element.parent().parent();
        var parent_id = parent.attr('data-item-id');
        return parent_id;
    },
    getOrder: function (element) {
        var parent = element.parent();
        var index = parent.find("> li.dd-item").index(element);
        return (index == null) ? 1 : index + 1;
    },
    getEditedObject: function () {
        let item = {};
        let formArray = this.formModalForm.serializeArray();
        for(element of formArray) {
            item[element.name] = element.value;
        }
        return item;
    },
    init: function () {
        var self = this;

        // clear and draw
        self.redraw();

        // make nestable
        self.container.nestable({
            maxDepth: 2,
            expandBtnHTML: "",
            collapseBtnHTML: ""
        });

        // broadcast item reorder change to callback
        self.container.on('change',function () {
            var items = [];
            self.container.find('ul.dd-list li.dd-item').each(function(key, val){
                var element = $(this);
                items.push({ id: element.attr("data-item-id"), parent_id: self.getParentId(element), order: self.getOrder(element)});
            });
            self.orderCallback(items);
        });

        self.newItemButton.click(function () {
            self.showCreateModal();
        })
    },
    redraw: function () {
        this.container.empty();
        this.draw();
    },
    draw: function (struct = false, root = false) {
        var self = this;
        struct = struct ? struct : self.data;
        root = root ? root : self.container;
        let ul = $('<ul/>').attr({ class: 'dd-list' });
        for(i in struct) {
            let item = struct[i];
            let li = $('<li/>').attr({ class: 'dd-item dd3-item', 'data-item-id': item.id });
            let handle = $('<div/>').attr({ class: 'dd-handle dd3-handle' }).append('Drag');
            let content = $('<div/>').attr({ class: 'dd-content dd3-content' });
            if(item.display < 1) content.addClass('text-muted');
            let contentIcon = $('<i/>').attr({ class: item.icon_class });
            let contentText = '&nbsp;' + item.text
            let contentActionGroup = $('<span/>').attr({ class: 'float-right' });
            if(item.id) {
                if(self.canUpdate) {
                    let contentActionEdit = $('<a/>').attr({ class: 'menu-action text-dark', href: '#'}).append($('<i/>').attr({ class: 'fa fa-edit' }));
                    contentActionEdit.on('click', function () {
                        self.showUpdateModal(item.id);
                    })
                    contentActionGroup.append(contentActionEdit);
                }
                if(self.canDelete) {
                    let contentActionDelete = $('<a/>').attr({ class: 'menu-action text-danger', href: '#'}).append($('<i/>').attr({ class: 'fas fa-window-close' }));
                    contentActionDelete.on('click', function () {
                        self.delete(item.id);
                    })
                    contentActionGroup.append(contentActionDelete);
                }
            }
            
            content.append(contentIcon);
            content.append(contentText);
            content.append(contentActionGroup);
            li.append(handle);
            li.append(content);
            if(item.children) {
                self.draw(item.children, li);
            }
            ul.append(li);
        }
        root.append(ul);
        return ul;
    }
};