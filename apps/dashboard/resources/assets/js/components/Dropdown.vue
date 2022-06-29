<template>
    <div class="dropdown">
        <button class="btn btn-default dropdown-toggle" @click="toggleMenu()" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span v-if="selectedOption.label !== undefined">{{ selectedOption.label }}</span>
            <span v-else>{{ placeholderText }}</span>
        </button>
        <div class="dropdown-menu dropdown-menu-right d-block" v-if="showMenu">
            <a  class="dropdown-item" href="javascript:void(0)" @click="updateOption(option)" v-for="option in options">
                {{ option.label }}
            </a>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            selectedOption: {},
            showMenu: false,
            placeholderText: 'Please select an item',
        };
    },
    props: {
        options: {
            type: [Array, Object]
        },
        selected: [Object],
        placeholder: [String],
    },
    mounted() {
        this.selectedOption = this.selected;
        if (this.placeholder) {
            this.placeholderText = this.placeholder;
        }
    },
    methods: {
        updateOption(option) {
            this.selectedOption = option;
            this.showMenu = false;
            this.$emit('updateOption', this.selectedOption);
        },
        toggleMenu() {
          this.showMenu = !this.showMenu;
        }
    },
};
</script>
