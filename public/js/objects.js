/* clean array and remove all values equal to deleteValue */
Array.prototype.clean = function(deleteValue) {
	var temp = this;
  for (var i = 0; i < temp.length; i++) {
    if (this[i] == deleteValue) {   
      temp.splice(i, 1);
      i--;
    }
  }
  return temp;
};

/* extend Date to retrieve days of week */
var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

Date.prototype.getMonthName = function() {
	return months[ this.getMonth() ];
};
Date.prototype.getDayName = function() {
	return days[ this.getDay() ];
};

var Class = function(opts)
{
	for (var key in opts) {
		// important check that this is objects own property 	
		this[key] = opts[key];
	}
}


/* User object */
var User = function(opts) 
{
	this.getShortName = function() {
							return this.firstName + ' ' + this.lastName[0];
						}
	
	Class.call(this, opts);
};


/* Game object */
var Game = function(opts) 
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
						
	
	this.removePlayer = function(userID) {
							var index = this.playerIDs[userID];
							this.players[index] = null;
							
	}
	
	this.getRandomPlayers = function(numReturned, playersArray) {
								
								if (typeof playersArray == 'undefined') {
									// Array of players hant  been given, default to this.players
									playersArray = this.players;
								}
								
									
								var returnArray = new Array();
								var limit = numReturned;
								
								var numPlayers = playersArray.length;
								
								if (numPlayers < numReturned) {
									// Not enough players
									return false;
									// limit = playersArray.length; // uncomment if want to return players despite not enough
								}
								var used = new Array();

								for (var i = 0; i < limit; i++) {
									var num = getRandomInt(1,numPlayers, used);
									used.push(num);
					
									returnArray.push(playersArray[num - 1]);
								}
								return returnArray;
							}
							
	this.getRandomSportRating = function(ratingsArray) {
								if (typeof ratingsArray == 'undefined') {
									// Array of players hant  been given, default to this.players
									
									ratingsArray = this.sportRatings;
								}
								
								if (ratingsArray.length < 1) {
									return false;
								}
														
								var limit = ratingsArray.length
								
								var num = getRandomInt(1, limit);
								
								return ratingsArray[num - 1];
							}
					
	this.addPlayer = function(opts) {
							var user = new User(opts);
							if (!(this.players instanceof Array)) {
								// Not array of players yet
								this.players = new Array();
								this.playerIDs = new Array();
							}
							
							this.players.push(user);
							this.playerIDs[user.userID] = this.players.length - 1; // Store location of user for players array in playerIDs
						}
						
	this.addRating = function(opts) {
							var rating = new Rating(opts);
							if (!(this.sportRatings instanceof Array)) {
								// Not array of players yet
								this.sportRatings = new Array();
								this.sportRatingIDs = new Array();
							}
							
							this.sportRatings.push(rating);
							this.sportRatingIDs[rating.sportRatingID] = this.sportRatings.length - 1; // Store location of user for players array in playerIDs
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
var Rating = function(opts) 
{
	
	Class.call(this, opts);
};

var RelativeRating = function(opts) 
{
	
	Class.call(this, opts);
};

