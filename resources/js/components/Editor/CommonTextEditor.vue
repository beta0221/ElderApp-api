<template>
  <ckeditor :editor="editor" v-model="content_text"></ckeditor>
</template>

<script>
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
export default {
    props: ['content_text'],
    data(){
        return{
            editor:ClassicEditor,
        }
    }
}
</script>

<style>

</style>