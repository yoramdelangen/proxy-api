if (Meteor.connection._mongo_livedata_collections || false) {
    window.conn = Meteor.connection._mongo_livedata_collections;

    console.log('Waited untill found..');
    console.log(window.conn)

    window.Meteor.subscribe('podcast_list', function() {
        window.fetchedPodcasts = window.conn.Podcast.find().fetch()
        window.fetchedTags = window.conn.Tags.find().fetch()
    })
}
