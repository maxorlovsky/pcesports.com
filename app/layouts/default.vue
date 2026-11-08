<script lang="ts">
import SiteLayout from '~/domains/site/views/SiteLayout.vue';

// A render function hands the page to the site's layout as is: a template's <slot /> would wrap it in one more fragment.
export default defineComponent((_props, { slots }) => () => h(SiteLayout, null, slots));
</script>
