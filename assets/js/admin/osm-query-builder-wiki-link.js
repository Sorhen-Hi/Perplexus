export default {
    props: ['condition'],
    computed: {
        isTag() { 
            if (this.condition.key && (this.condition.operator == '=') && this.condition.value) 
                return true
            return false
        },
        url() {
            if (this.isTag) {
                return `https://wiki.openstreetmap.org/wiki/Tag:${this.tag}`
            } else {
                return `https://wiki.openstreetmap.org/wiki/Key:${this.condition.key}`
            }
        },
        tag() {
            return `${this.condition.key}=${this.condition.value}`
        },
        title() {
            return `Lien vers la fiche Wiki de "${this.isTag ? this.tag : this.condition.key}"` 
        }
    },
    template: `
        <a :href="url" v-if="condition.key" class="btn btn-default btn-icon" 
            target="_blank" :title="title">
            <i class="fa fa-external-link-square"></i>
        </a>
    `
}