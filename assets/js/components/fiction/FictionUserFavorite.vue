<template class="favorite">
    <ApolloQuery tag="span" :query="require('../../graphql/queries/FictionUserFavoriteQuery.graphql')" :variables="{fictionId}">
        <template slot-scope="{result, loading, gqlError}">
            <ApolloMutation
                    v-if="result && result.data && result.data.fiction.viewerHasFavorited"
                    :mutation="require('../../graphql/mutations/DeleteFictionUserFavoriteMutation.graphql')"
                    :variables="{input: {fictionId}}"
                    :optimistic-response="{
                    __typename: 'Mutation',
                    deleteFictionUserFavorite: {
                            __typename: 'DeleteFictionUserFavoritePayload',
                            fiction: {
                                __typename: 'Fiction',
                                id: fictionId,
                                viewerHasFavorited: false
                            }
                        }
                    }"
                    tag="span"
            >
                <template slot-scope="{mutate, loading, gqlError}">
                    <button class="delete-favorite fa fa-star" :disabled="loading" @click="mutate"></button>
                </template>
            </ApolloMutation>
            <ApolloMutation
                    v-else-if="result && result.data && !result.data.fiction.viewerHasFavorited"
                    :mutation="require('../../graphql/mutations/AddFictionUserFavoriteMutation.graphql')"
                    :variables="{input: {fictionId}}"
                    :optimistic-response="{
                    __typename: 'Mutation',
                    addFictionUserFavorite: {
                            __typename: 'AddFictionUserFavoritePayload',
                            fiction: {
                                __typename: 'Fiction',
                                id: fictionId,
                                viewerHasFavorited: true
                            }
                        }
                    }"
                    tag="span"
            >
                <template slot-scope="{mutate, loading, gqlError}">
                    <button class="add-favorite fa fa-star" :disabled="loading" @click="mutate"></button>
                </template>
            </ApolloMutation>
        </template>
    </ApolloQuery>
</template>

<script>
    export default {
        name: "FictionUserFavorite",
        props: {
            fictionId: {type: String, required: true}
        }
    }

</script>

<style lang="scss" scoped>
    .add-favorite {
        color: grey;
        border: none;
        background-color: whitesmoke;
        transition: $defaultTransitionDuration;
    }
    .delete-favorite {
        color: #ffe533;
        border: none;
        background-color: whitesmoke;
        transition: $defaultTransitionDuration;
    }
</style>