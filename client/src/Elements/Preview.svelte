<svelte:options tag="iola-preview" />

<style>
    .even {
        color: red;
    }

    .item {
        margin: 5px;
    }
</style>

<script>
    /**
     * Element props
     */
    export let label = 'Add';

    /**
     * Local state 
     */
    let items = [];

    /**
     * Global methods
     */
    export const addItem = (itemLabel = 'New Item') => {
        items = items.concat(itemLabel);
    };
</script>

<div class="root">
    <input type="button" value={label} on:click|stopPropagation={() => addItem()} />

    <div class="list">
        {#each items as itemLabel, index}
            <div class="item" class:even={index % 2}>
                #{index + 1}: {itemLabel}
            </div>
        {/each}
    </div>
</div>