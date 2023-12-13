cd "/Users/charlesdavies/documents/tennis web app/src"
TARGET="/opt/homebrew/var/www/Tennis"
cp -v -p Home/Home.html $TARGET
cp -v -p Series/*.php $TARGET
cp -v -p Series/*.html $TARGET
cp -v -p Users/*.php $TARGET
cp -v -p Users/*.html $TARGET
cp -v -p Fixture/*.php $TARGET
cd "/Users/charlesdavies/documents/tennis web app"
cp -v -p Library/*.php $TARGET