function Class(opts)
{
	for (var key in opts) {
		// important check that this is objects own property 	
		this[key] = opts[key];
	}
}


/* User object */
function User(opts) 
{
	this.getShortName = function() {
							return this.firstName + ' ' + this.lastName[0];
						}
	
	Class.call(this, opts);
};


/* Game object */
function Game(opts) 
{
	this.isPickupGame = function() {
							if (typeof this.gameID != 'undefined') {
								// Is pickup game
								return true;
							}
							return false;
						}
						
	this.isTeamGame = function() {
							if (typeof this.teamGameID != 'undefined') {
								// Is team game
								return true;
							}
							return false;
						}
	
	this.getRandomPlayers = function(numReturned) {
								
								var returnArray = new Array();
								var limit = numReturned;
								var numPlayers = this.players.length;
								
								if (numPlayers < numReturned) {
									limit = this.players.length;
								}
								var used = new Array();

								for (var i = 0; i < limit; i++) {
									var num = getRandomInt(1,numPlayers, used);
									used.push(num);
					
									returnArray.push(this.players[num - 1]);
								}
								return returnArray;
							}
					
	this.addPlayer = function(opts) {
							var user = new User(opts);
							if (!(this.players instanceof Array)) {
								// Not array of players yet
								this.players = new Array();
							}
							
							this.players.push(user);
						}
						
	this.addRating = function(opts) {
							var rating = new Rating(opts);
							if (!(this.sportRatings instanceof Array)) {
								// Not array of players yet
								this.sportRatings = new Array();
							}
							
							this.sportRatings.push(rating);
						}
						
	this.getGameDate = function() {
							if (typeof this.gameDate == 'undefined') {
								var date = new Date(this.date.substring(0,4),this.date.substring(5,7), this.date.substring(8,10), this.date.substring(11,13), this.date.substring(14,16));
								this.gameDate = date;
							}
							
							return this.gameDate;
						}
	
	Class.call(this, opts);
};

/* Rating object */
function Rating(opts) 
{
	
	Class.call(this, opts);
};