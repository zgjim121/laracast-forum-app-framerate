<template>
    <Head>
        <link rel="canonical" :href="post.routes.show"/>
    </Head>

    <AppLayout :title="post.title">
        <Container>
            <Pill :href="route('posts.index', {topic: post.topic.slug})">{{ post.topic.name }}</Pill>
            <PageHeading class="mt-2">{{ post.title }}</PageHeading>
            <span class="block mt-1 text-sm text-gray-600"
                  v-if="post.created_at">{{ formattedDate }} by {{ post.user.name }}
            </span>

            <div class="mt-4">
                <span class="text-pink-500 font-bold">{{ post.likes_count }} likes</span>
                <div class="mt-2" v-if="$page.props.auth.user">
                    <Link v-if="post.can.like" :href="route('likes.store', ['post', post.id])"
                          method="post"
                          class="inline-block bg-indigo-600 hover:bg-pink-500
                          transition-colors text-white py-1.5 px-3 rounded-full">

                        <HandThumbUpIcon class="size-4 inline-block mr-1"/>
                        Like the Post
                    </Link>
                    <Link v-else :href="route('likes.destroy', ['post', post.id])"
                          method="delete"
                          class="inline-block bg-indigo-600 hover:bg-pink-500
                          transition-colors text-white py-1.5 px-3 rounded-full">

                        <HandThumbDownIcon class="size-4 inline-block mr-1"/>
                        Unlike the Post
                    </Link>
                </div>
            </div>

            <article class="mt-6 prose prose-sm max-w-none"
                     v-html="post.html">
            </article>
            <div class="mt-12">
                <h2 class="text-xl font-semibold">Comments</h2>

                <form v-if="$page.props.auth.user"
                      @submit.prevent="() => commentIdBeingEdited ? updateComment() : addComment()"
                      class="mt-4 ">
                    <div>
                        <InputLabel for="body" class="sr-only">Comment</InputLabel>
                        <MarkdownEditor ref="commentTextAreaRef"
                                        id="body"
                                        v-model="commentForm.body"
                                        placeholder="Share your thoughts..."
                                        editorClass="!min-h-[160px]"/>
                        <InputError :message="commentForm.errors.body"
                                    class="mt-1"/>
                    </div>
                    <PrimaryButton
                        type="submit"
                        class="mt-3"
                        :disabled="commentForm.processing"
                        v-text="commentIdBeingEdited ? 'Update Comment' : 'Add Comment'">
                    </PrimaryButton>
                    <SecondaryButton v-if="commentIdBeingEdited"
                                     @click="cancelEditComment"
                                     class="ml-2 ">Cancel
                    </SecondaryButton>
                </form>
                <ul class="divide-y mt-4">
                    <li v-for="comment in comments.data"
                        :key="comment.id"
                        class="px-2 py-4">
                        <Comment @edit="editComment"
                                 @delete="deleteComment"
                                 :comment="comment">
                        </Comment>
                    </li>
                </ul>
                <Pagination :meta="comments.meta" :only="['comments']"/>
            </div>
        </Container>
    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import {computed, ref} from "vue";
import Container from "@/Components/Container.vue";
import Pagination from "@/Components/Pagination.vue";
import {relativeDate} from "@/Utilities/date.js"
import Comment from "@/Components/Comment.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Head, Link, router, useForm} from "@inertiajs/vue3";
import TextArea from "@/Components/TextArea.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {useConfirm} from "@/Utilities/Composables/useConfirm.js";
import MarkdownEditor from "@/Components/MarkdownEditor.vue";
import PageHeading from "@/Components/PageHeading.vue";
import Pill from "@/Components/Pill.vue";
import {HandThumbUpIcon, HandThumbDownIcon} from "@heroicons/vue/20/solid";


const props = defineProps(['post', 'comments']);
const formattedDate = computed(() => relativeDate(props.post.created_at));

const commentForm = useForm({
    body: '',
});
const commentTextAreaRef = ref(null);
const commentIdBeingEdited = ref(null);
const commentBeingEdited = computed(() => props.comments.data.find(comment => comment.id === commentIdBeingEdited.value))
const editComment = (commentId) => {
    commentIdBeingEdited.value = commentId;
    commentForm.body = commentBeingEdited.value?.body;
    commentTextAreaRef.value?.focus();
};

const cancelEditComment = () => {
    commentIdBeingEdited.value = null;
    commentForm.reset();
}

const addComment = () => commentForm.post(route('posts.comments.store', props.post.id), {
    preserveScroll: true,
    onSuccess: () => commentForm.reset(),
});

const {confirmation} = useConfirm();

const updateComment = async () => {

    if (!await confirmation('Are u sure u want to update this comment')) {
        commentTextAreaRef.value?.focus();
        return;
    }

    commentForm.put(route('comments.update', {
        comment: commentIdBeingEdited.value,
        page: props.comments.meta.current_page,
    }), {
        preserveScroll: true,
        onSuccess: cancelEditComment
    });
}

const deleteComment = async (commentId) => {

    if (!await confirmation('Are u sure u want to delete this comment')) {
        return;
    }

    router.delete(
        route('comments.destroy', {
            comment: commentId,
            page: props.comments.meta.length > 1
                ? props.comments.meta.current_page
                : Math.max(props.comments.meta.length - 1, 1),
        }),
        {
            preserveScroll: true,
            onSuccess: cancelEditComment
        });
};


</script>
