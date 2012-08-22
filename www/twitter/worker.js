var util = require('util'),
    twitter = require('twitter'),
    querystring = require('querystring'),
    mysql = require('mysql');

var num_tweets = '100',  //max is 100
    num_pages = 15, // pages * rpp = total number of tweets to search for
    tweet_type = 'recent';  // mixed: Include both popular and real time results in the response.
			     // recent: return only the most recent results in the response
			     // popular: return only the most popular results in the response. 

var searchTerms = ['nodejs OR #node', 'obama'];

var twit = new twitter({
    consumer_key: 'Esv5Nob0gSQeMEXOyHikMw',
    consumer_secret: 'bJErFw11l4z8Y4wq4kjlCvcZEa3Mu1P74RR08WBRS7k',
    access_token_key: '14666773-Jw7HAwYGE21anOOaxguayRZFCRBH3CdLSWphi4Gi5',
    access_token_secret: 'LGvDeJmqtPTp28YcoBYLhKQU92nq90DVjdp5QbzCUoA'
});

var rootSearchTimeout = 1000*60*10;  //10 minutes
var rootSearches = [];  //all search terms with twitter parameters set
var urlsToGet = [];  //temp array that is iterated through to get all the twitter search pages since twitter limits 100 tweets returned for each query


//first build all the twitter queries from the searchTerms array
// ?page=2&max_id=235722516608454657&q=nodejs%20OR%20%23node&rpp=1&result_type=recent
searchTerms.forEach(function(term) {
  var db = mysql.createConnection({
    host        : 'localhost',
    database    : 'museum',
    user        : 'root',
    password    : 'root',

  });
  db.connect();
  db.query('SELECT `id` FROM `json_tweets` WHERE search_query = ? order by id desc limit 1', [term.replace(/ /g,"+").replace(/#/g,"%23")], function(err, result) {
    var since_id = '0';
    if (result.length > 0) {
      since_id = result[0].id;
    } 
  rootSearches.push('?page=1&q='+term+'&rpp='+num_tweets+'&result_type='+tweet_type+'&since_id='+since_id); 
  });
  db.end();
//  startRootSearches();
});



//start all the root search to twitter. restart the root search every 10 mins
function startRootSearches() {
  //first copy the array then clean the orginal for reuse
  var t_rSearches = rootSearches;
  rootSearches = [];
  t_rSearches.forEach(function(url) {
  console.log('processNextURL '+url);
    var query = querystring.parse(url.substring(1));
    var tq = query.q;
    delete(query.q);
    twit.search(tq,query,function(data) {
      processTweets(data);
      urlsToGet.push(data.next_page);
      rootSearches.push('?page=1&q='+tq+'&rpp='+num_tweets+'&result_type='+tweet_type+'&since_id='+data.max_id);
    });
  });
}

setTimeout(startRootSearches,5000); //delay the first call for 5 seconds to allow db lookup
setInterval(startRootSearches,rootSearchTimeout);

//  process all the next pages from the twitter search page results.  pops twitter query from urlsToGet array. fires every 3 seconds
setInterval(function() {
  var url = urlsToGet.shift();
  console.log('processNextURL '+url);
  if (typeof url != 'undefined') {
    var query = querystring.parse(url.substring(1));
    var tq = query.q;
    delete(query.q);
    if (query.page < num_pages) {
      twit.search(tq,query,function(data) {
        processTweets(data);
        urlsToGet.push(data.next_page);
      });
    }    
  }
},3000);


function processTweets(tweets) {
var db = mysql.createConnection({
    host	: 'localhost',
    database	: 'museum',
    user	: 'root',
    password	: 'root',
   
});
  db.connect();
  for (var i=0; i<tweets.results.length; i++) {
    console.log(tweets);
    //console.log('//////////');
    //console.log(tweets.query);
    //console.log(tweets.results[i].created_at);
    //console.log(tweets.results[i].id);
    //console.log(tweets.results[i].text);
    //console.log(tweets.results[i]);
    var data = { id : tweets.results[i].id, created_at : new Date(tweets.results[i].created_at), created_at_date : new Date(tweets.results[i].created_at), search_query : tweets.query, raw_tweet : tweets.results[i], text : tweets.results[i].text };
    db.query('INSERT INTO json_tweets SET ?', data, function(err, results) {

    }); 
  }
  db.end();
}

