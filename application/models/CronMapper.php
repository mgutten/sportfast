<?php

class Application_Model_CronMapper extends Application_Model_MapperAbstract
{
	protected $_dbTableClass = 'Application_Model_DbTable_Users';


	public function moveGamesToOld()
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$insertGames = "INSERT INTO old_games (gameID, parkID, parkName, backupParkID, backupParkName, 
											   public, sport, sportID, typeID, rosterLimit, maxSkill, 
											   minSkill, maxAge, minAge, recurring, date, city, cityID, 
											   minPlayers, canceled, cancelReason, remove, totalPlayers, movedDate) 
								(SELECT g.gameID, g.parkID, g.parkName, g.backupParkID, g.backupParkName,
									    g.public, g.sport, g.sportID, g.typeID, g.rosterLimit, g.maxSkill,
										g.minSkill, g.maxAge, g.minAge, g.recurring, g.date, g.city, g.cityID,
										g.minPlayers, g.canceled, g.cancelReason, g.remove, COUNT(ug.userID), now()
									FROM games g 
									LEFT JOIN user_games ug ON ug.gameID = g.gameID 
									WHERE g.date < now() 
									GROUP BY g.gameID)";
		$db->query($insertGames);
		$oldGameID = $db->lastInsertId();
								
		$insertUserGames = "INSERT INTO old_user_games (oldGameID, gameID, userID, plus)
								(SELECT og.oldGameID, ug.gameID, ug.userID, ug.plus
									FROM user_games ug
									INNER JOIN (SELECT * 
													FROM old_games 
													WHERE movedDate = (SELECT movedDate 
																		FROM old_games 
																		WHERE oldGameID = '" . $oldGameID . "')
												) og ON og.gameID = ug.gameID)";
												
		$db->query($insertUserGames);
		
		$deleteGames = "DELETE FROM games 
							WHERE games.gameID IN (SELECT gameID 
													FROM old_games 
													WHERE movedDate = (SELECT movedDate 
																		FROM old_games 
																		WHERE oldGameID = '" . $oldGameID . "')
														AND recurring = 0
												   )";
		$db->query($deleteGames);
		
		$updateGames = "UPDATE games SET date = DATE_ADD(date,INTERVAL 1 WEEK),
										 canceled = 0,
										 cancelReason = '' 
							WHERE date < now()";
							
		$db->query($updateGames);
		
		$deleteUserGames = "DELETE FROM user_games 
							WHERE user_games.gameID IN (SELECT gameID 
													FROM old_games 
													WHERE movedDate = (SELECT movedDate 
																		FROM old_games 
																		WHERE oldGameID = '" . $oldGameID . "')
												   )";
												   
		$db->query($deleteUserGames);
									
		
	}
	
}