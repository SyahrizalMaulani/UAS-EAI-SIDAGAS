const { buildSchema } = require('graphql');

const schema = buildSchema(`
    type Item {
        id: ID!
        item_name: String!
        stock: Int!
        updated_at: String!
    }

    type Query {
        getInventory: [Item]
        checkStock(item_name: String!): Item
    }

    type Mutation {
        updateStock(item_name: String!, amount: Int!): Item
        deleteStock(item_name: String!): String
    }
`);

module.exports = schema;
