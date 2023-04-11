cd "/Users/charlesdavies/documents/tennis web app/WebPages"
TARGET="/opt/homebrew/var/www/Tennis"
cp -v -p Home/Home.html $TARGET
cp -v -p ListSeries/*.php $TARGET
cp -v -p ListSeries/*.html $TARGET
cp -v -p ListUsers/*.php $TARGET
cp -v -p DeleteUser/*.php $TARGET
cp -v -p Users/*.php $TARGET
cp -v -p Users/*.html $TARGET